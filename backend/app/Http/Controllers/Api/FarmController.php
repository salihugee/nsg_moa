<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFarmRequest;
use App\Http\Requests\UpdateFarmRequest;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FarmController extends Controller
{
    /**
     * Display a listing of farms with optional filtering and pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Farm::query()
                ->with(['farmer', 'crops'])
                ->when($request->farmer_id, fn($q, $farmerId) => $q->where('farmer_id', $farmerId))
                ->when($request->soil_type, fn($q, $soilType) => $q->where('soil_type', $soilType))
                ->when($request->water_source, fn($q, $waterSource) => $q->where('water_source', $waterSource))
                ->when($request->min_size, fn($q, $minSize) => $q->where('size_hectares', '>=', $minSize))
                ->when($request->max_size, fn($q, $maxSize) => $q->where('size_hectares', '<=', $maxSize));

            $farms = $query->paginate($request->per_page ?? 15);

            return response()->json($farms);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving farms',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created farm.
     *
     * @param StoreFarmRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreFarmRequest $request)
    {
        try {
            $farm = Farm::create($request->validated());

            return response()->json([
                'message' => 'Farm created successfully',
                'data' => $farm->load(['farmer', 'crops'])
            ], Response::HTTP_CREATED);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error creating farm',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified farm.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $farm = Farm::with(['farmer', 'crops'])->findOrFail($id);

            return response()->json($farm);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Farm not found',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified farm.
     *
     * @param UpdateFarmRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateFarmRequest $request, string $id)
    {
        try {
            $farm = Farm::findOrFail($id);
            $farm->update($request->validated());

            return response()->json([
                'message' => 'Farm updated successfully',
                'data' => $farm->load(['farmer', 'crops'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating farm',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified farm.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $farm = Farm::findOrFail($id);
            $farm->delete();

            return response()->json([
                'message' => 'Farm deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting farm',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Find farms that intersect with a given region (polygon).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function inRegion(Request $request)
    {
        $request->validate([
            'region' => 'required|array',
            'region.type' => 'required|string|in:Polygon',
            'region.coordinates' => 'required|array',
        ]);

        try {
            $farms = Farm::whereRaw('ST_Intersects(
                    ST_SetSRID(ST_GeomFromGeoJSON(?), 4326),
                    boundaries
                )', [json_encode($request->region)])
                ->with(['farmer', 'crops'])
                ->get();

            return response()->json($farms);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error finding farms in region',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Filter farms by size range.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bySize(Request $request)
    {
        $request->validate([
            'min_size' => 'required|numeric|min:0',
            'max_size' => 'required|numeric|gt:min_size',
        ]);

        try {
            $farms = Farm::whereBetween('size_hectares', [$request->min_size, $request->max_size])
                ->with(['farmer', 'crops'])
                ->get();

            return response()->json($farms);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error filtering farms by size',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get farm statistics by various metrics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_farms' => Farm::count(),
                'total_area' => Farm::sum('size_hectares'),
                'avg_farm_size' => Farm::avg('size_hectares'),
                'by_soil_type' => Farm::groupBy('soil_type')
                    ->select('soil_type', DB::raw('count(*) as count'))
                    ->get(),
                'by_water_source' => Farm::groupBy('water_source')
                    ->select('water_source', DB::raw('count(*) as count'))
                    ->get(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving farm statistics',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
