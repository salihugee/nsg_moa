<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFarmerRequest;
use App\Http\Requests\UpdateFarmerRequest;
use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Farmers",
 *     description="API Endpoints for managing farmers"
 * )
 */
class FarmerController extends Controller
{
    /**
     * Display a listing of farmers with optional filtering and pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Get(
     *     path="/api/v1/farmers",
     *     summary="Get list of farmers",
     *     tags={"Farmers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of farmers",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Farmer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = Farmer::query()
                ->with(['farms', 'livestock'])
                ->when($request->status, fn($q, $status) => $q->where('status', $status))
                ->when($request->search, function($q, $search) {
                    $q->where(function($query) use ($search) {
                        $query->where('full_name', 'like', "%{$search}%")
                            ->orWhere('registration_number', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                });

            $farmers = $query->paginate($request->per_page ?? 15);

            return response()->json($farmers);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving farmers',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created farmer.
     *
     * @param StoreFarmerRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *     path="/api/v1/farmers",
     *     summary="Create a new farmer",
     *     tags={"Farmers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreFarmerRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Farmer created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Farmer")
     *     )
     * )
     */
    public function store(StoreFarmerRequest $request)
    {
        try {
            $farmer = Farmer::create($request->validated());

            return response()->json([
                'message' => 'Farmer created successfully',
                'data' => $farmer->load(['farms', 'livestock'])
            ], Response::HTTP_CREATED);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error creating farmer',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified farmer.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Get(
     *     path="/api/v1/farmers/{id}",
     *     summary="Get specific farmer",
     *     tags={"Farmers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Farmer details",
     *         @OA\JsonContent(ref="#/components/schemas/Farmer")
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $farmer = Farmer::with(['farms', 'livestock'])->findOrFail($id);

            return response()->json($farmer);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Farmer not found',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified farmer.
     *
     * @param UpdateFarmerRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Put(
     *     path="/api/v1/farmers/{id}",
     *     summary="Update farmer",
     *     tags={"Farmers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateFarmerRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Farmer updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Farmer")
     *     )
     * )
     */
    public function update(UpdateFarmerRequest $request, string $id)
    {
        try {
            $farmer = Farmer::findOrFail($id);
            $farmer->update($request->validated());

            return response()->json([
                'message' => 'Farmer updated successfully',
                'data' => $farmer->load(['farms', 'livestock'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating farmer',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified farmer.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Delete(
     *     path="/api/v1/farmers/{id}",
     *     summary="Delete farmer",
     *     tags={"Farmers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Farmer deleted successfully"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $farmer = Farmer::findOrFail($id);
            $farmer->delete();

            return response()->json([
                'message' => 'Farmer deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting farmer',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Find farmers within a specified radius of a point.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Get(
     *     path="/api/v1/farmers/nearby/{lat}/{lng}/{distance}",
     *     summary="Find farmers within distance",
     *     tags={"Farmers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="lat",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="lng",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="distance",
     *         in="path",
     *         description="Distance in meters",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of nearby farmers",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Farmer")
     *         )
     *     )
     * )
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric|min:0', // radius in kilometers
        ]);

        try {
            $farmers = Farmer::selectRaw('*, ST_Distance(
                    location_coordinates,
                    ST_SetSRID(ST_MakePoint(?, ?), 4326)
                ) as distance', [$request->longitude, $request->latitude])
                ->havingRaw('distance <= ?', [$request->radius * 1000]) // Convert km to meters
                ->orderBy('distance')
                ->with(['farms', 'livestock'])
                ->get();

            return response()->json($farmers);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error finding nearby farmers',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Find all farmers within a polygon area.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *     path="/api/v1/farmers/in-area",
     *     summary="Find farmers in area",
     *     tags={"Farmers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="area",
     *                 type="object",
     *                 description="GeoJSON polygon"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of farmers in area",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Farmer")
     *         )
     *     )
     * )
     */
    public function inArea(Request $request)
    {
        $request->validate([
            'polygon' => 'required|array',
            'polygon.type' => 'required|string|in:Polygon',
            'polygon.coordinates' => 'required|array',
        ]);

        try {
            $farmers = Farmer::whereRaw('ST_Contains(
                    ST_SetSRID(ST_GeomFromGeoJSON(?), 4326),
                    location_coordinates
                )', [json_encode($request->polygon)])
                ->with(['farms', 'livestock'])
                ->get();

            return response()->json($farmers);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error finding farmers in area',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get statistics about farmers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $stats = [
                'total' => Farmer::count(),
                'by_status' => Farmer::groupBy('status')
                    ->select('status', DB::raw('count(*) as count'))
                    ->get(),
                'avg_farm_size' => Farmer::avg('farm_size'),
                'total_farm_size' => Farmer::sum('farm_size'),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving farmer statistics',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
