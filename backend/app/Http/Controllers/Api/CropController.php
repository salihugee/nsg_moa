<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCropRequest;
use App\Http\Requests\UpdateCropRequest;
use App\Models\Crop;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class CropController extends Controller
{
    /**
     * Display a listing of crops with optional filtering and pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Crop::query()
                ->with(['farm.farmer'])
                ->when($request->farm_id, fn($q, $farmId) => $q->where('farm_id', $farmId))
                ->when($request->crop_type, fn($q, $cropType) => $q->where('crop_type', $cropType))
                ->when($request->status, fn($q, $status) => $q->where('status', $status))
                ->when($request->planted_after, fn($q, $date) => $q->where('planting_date', '>=', $date))
                ->when($request->planted_before, fn($q, $date) => $q->where('planting_date', '<=', $date))
                ->when($request->harvesting_soon, function($q) {
                    $q->where('status', '!=', 'harvested')
                        ->where('expected_harvest_date', '<=', Carbon::now()->addDays(30));
                });

            $crops = $query->paginate($request->per_page ?? 15);

            return response()->json($crops);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving crops',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created crop.
     *
     * @param StoreCropRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCropRequest $request)
    {
        try {
            $crop = Crop::create($request->validated());

            return response()->json([
                'message' => 'Crop created successfully',
                'data' => $crop->load('farm.farmer')
            ], Response::HTTP_CREATED);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error creating crop',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified crop.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $crop = Crop::with('farm.farmer')->findOrFail($id);

            return response()->json($crop);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Crop not found',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified crop.
     *
     * @param UpdateCropRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCropRequest $request, string $id)
    {
        try {
            $crop = Crop::findOrFail($id);
            $crop->update($request->validated());

            return response()->json([
                'message' => 'Crop updated successfully',
                'data' => $crop->load('farm.farmer')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating crop',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified crop.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $crop = Crop::findOrFail($id);
            $crop->delete();

            return response()->json([
                'message' => 'Crop deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting crop',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get crops by type with statistics.
     *
     * @param string $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function byType(string $type)
    {
        try {
            $crops = Crop::where('crop_type', $type)
                ->with('farm.farmer')
                ->get();

            $stats = [
                'total_count' => $crops->count(),
                'total_yield' => $crops->where('status', 'harvested')->sum('yield_quantity'),
                'average_yield' => $crops->where('status', 'harvested')->avg('yield_quantity'),
                'planted_area' => Farm::whereHas('crops', fn($q) => $q->where('crop_type', $type))
                    ->sum('size_hectares'),
                'by_status' => $crops->groupBy('status')
                    ->map(fn($group) => $group->count()),
            ];

            return response()->json([
                'crops' => $crops,
                'statistics' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving crops by type',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get crops that are due for harvest.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function harvestDue(Request $request)
    {
        $request->validate([
            'days' => 'nullable|integer|min:1|max:90'
        ]);

        $daysThreshold = $request->days ?? 30;

        try {
            $crops = Crop::where('status', '!=', 'harvested')
                ->where('expected_harvest_date', '<=', Carbon::now()->addDays($daysThreshold))
                ->with('farm.farmer')
                ->orderBy('expected_harvest_date')
                ->get();

            $groupedByWeek = $crops->groupBy(function($crop) {
                return Carbon::parse($crop->expected_harvest_date)->startOfWeek()->format('Y-m-d');
            });

            return response()->json([
                'total_count' => $crops->count(),
                'by_week' => $groupedByWeek->map(fn($group) => [
                    'count' => $group->count(),
                    'crops' => $group
                ])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving harvest due crops',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get crop statistics and analytics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_crops' => Crop::count(),
                'by_type' => Crop::groupBy('crop_type')
                    ->select('crop_type', DB::raw('count(*) as count'))
                    ->get(),
                'by_status' => Crop::groupBy('status')
                    ->select('status', DB::raw('count(*) as count'))
                    ->get(),
                'total_harvested' => Crop::where('status', 'harvested')->count(),
                'total_yield' => Crop::where('status', 'harvested')->sum('yield_quantity'),
                'monthly_plantings' => Crop::selectRaw('DATE_FORMAT(planting_date, "%Y-%m") as month, count(*) as count')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get(),
                'upcoming_harvests' => Crop::where('status', '!=', 'harvested')
                    ->where('expected_harvest_date', '>', Carbon::now())
                    ->count()
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving crop statistics',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
