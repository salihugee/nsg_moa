<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLivestockRequest;
use App\Http\Requests\UpdateLivestockRequest;
use App\Models\Livestock;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class LivestockController extends Controller
{
    /**
     * Display a listing of livestock with optional filtering and pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Livestock::query()
                ->with(['farmer'])
                ->when($request->farmer_id, fn($q, $farmerId) => $q->where('farmer_id', $farmerId))
                ->when($request->animal_type, fn($q, $type) => $q->where('animal_type', $type))
                ->when($request->health_status, fn($q, $status) => $q->where('health_status', $status))
                ->when($request->min_quantity, fn($q, $min) => $q->where('quantity', '>=', $min))
                ->when($request->max_quantity, fn($q, $max) => $q->where('quantity', '<=', $max));

            $livestock = $query->paginate($request->per_page ?? 15);

            return response()->json($livestock);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving livestock records',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created livestock record.
     *
     * @param StoreLivestockRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreLivestockRequest $request)
    {
        try {
            $livestock = Livestock::create($request->validated());

            return response()->json([
                'message' => 'Livestock record created successfully',
                'data' => $livestock->load('farmer')
            ], Response::HTTP_CREATED);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error creating livestock record',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified livestock record.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $livestock = Livestock::with('farmer')->findOrFail($id);

            return response()->json($livestock);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Livestock record not found',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified livestock record.
     *
     * @param UpdateLivestockRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateLivestockRequest $request, string $id)
    {
        try {
            $livestock = Livestock::findOrFail($id);
            $livestock->update($request->validated());

            return response()->json([
                'message' => 'Livestock record updated successfully',
                'data' => $livestock->load('farmer')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating livestock record',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified livestock record.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $livestock = Livestock::findOrFail($id);
            $livestock->delete();

            return response()->json([
                'message' => 'Livestock record deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting livestock record',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get livestock by type with detailed statistics.
     *
     * @param string $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function byType(string $type)
    {
        try {
            $livestock = Livestock::where('animal_type', $type)
                ->with('farmer')
                ->get();

            $stats = [
                'total_count' => $livestock->count(),
                'total_quantity' => $livestock->sum('quantity'),
                'by_health_status' => $livestock->groupBy('health_status')
                    ->map(fn($group) => [
                        'count' => $group->count(),
                        'total_quantity' => $group->sum('quantity')
                    ]),
                'by_farmer' => $livestock->groupBy('farmer_id')
                    ->map(fn($group) => [
                        'farmer' => $group->first()->farmer->full_name,
                        'count' => $group->count(),
                        'total_quantity' => $group->sum('quantity')
                    ])
            ];

            return response()->json([
                'livestock' => $livestock,
                'statistics' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving livestock by type',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Find livestock within a specified radius.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric|min:0', // radius in kilometers
        ]);

        try {
            $livestock = Livestock::selectRaw('*, ST_Distance(
                    location,
                    ST_SetSRID(ST_MakePoint(?, ?), 4326)
                ) as distance', [$request->longitude, $request->latitude])
                ->havingRaw('distance <= ?', [$request->radius * 1000]) // Convert km to meters
                ->orderBy('distance')
                ->with('farmer')
                ->get();

            return response()->json([
                'count' => $livestock->count(),
                'total_quantity' => $livestock->sum('quantity'),
                'livestock' => $livestock
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error finding nearby livestock',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get health status report of livestock.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function healthReport()
    {
        try {
            $report = [
                'total' => Livestock::count(),
                'total_quantity' => Livestock::sum('quantity'),
                'by_health_status' => Livestock::groupBy('health_status')
                    ->select('health_status', 
                        DB::raw('count(*) as count'),
                        DB::raw('sum(quantity) as total_quantity'))
                    ->get(),
                'by_type' => Livestock::groupBy('animal_type')
                    ->select('animal_type',
                        DB::raw('count(*) as count'),
                        DB::raw('sum(quantity) as total_quantity'),
                        DB::raw('sum(case when health_status = "healthy" then quantity else 0 end) as healthy_quantity'),
                        DB::raw('sum(case when health_status != "healthy" then quantity else 0 end) as unhealthy_quantity'))
                    ->get(),
                'quarantined_locations' => Livestock::where('health_status', 'quarantined')
                    ->with('farmer')
                    ->get()
            ];

            return response()->json($report);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating health report',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get overall livestock statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_records' => Livestock::count(),
                'total_quantity' => Livestock::sum('quantity'),
                'by_type' => Livestock::groupBy('animal_type')
                    ->select('animal_type', 
                        DB::raw('count(*) as count'),
                        DB::raw('sum(quantity) as total_quantity'))
                    ->get(),
                'by_health_status' => Livestock::groupBy('health_status')
                    ->select('health_status', 
                        DB::raw('count(*) as count'),
                        DB::raw('sum(quantity) as total_quantity'))
                    ->get(),
                'top_farmers' => Livestock::groupBy('farmer_id')
                    ->select('farmer_id',
                        DB::raw('count(*) as count'),
                        DB::raw('sum(quantity) as total_quantity'))
                    ->with('farmer:id,full_name')
                    ->orderByDesc('total_quantity')
                    ->limit(10)
                    ->get(),
                'density_areas' => Livestock::selectRaw('
                        ST_AsGeoJSON(ST_ConvexHull(ST_Collect(location))) as area,
                        count(*) as count,
                        sum(quantity) as total_quantity
                    ')
                    ->groupBy('animal_type')
                    ->get()
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving livestock statistics',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
