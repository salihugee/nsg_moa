<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Farmer;
use App\Models\Farm;
use App\Models\Project;
use App\Models\WeatherData;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/analytics/dashboard",
     *     summary="Get dashboard analytics",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard analytics data",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_farmers", type="integer"),
     *             @OA\Property(property="total_farms", type="integer"),
     *             @OA\Property(property="total_farm_area", type="number"),
     *             @OA\Property(property="active_projects", type="integer")
     *         )
     *     )
     * )
     */
    public function dashboard()
    {
        $totalFarmers = Farmer::count();
        $totalFarms = Farm::count();
        $totalFarmArea = Farm::sum('size_hectares');
        $activeProjects = Project::where('status', 'active')->count();

        return response()->json([
            'total_farmers' => $totalFarmers,
            'total_farms' => $totalFarms,
            'total_farm_area' => $totalFarmArea,
            'active_projects' => $activeProjects
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/analytics/spatial-distribution",
     *     summary="Get spatial distribution of farmers and farms",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Spatial distribution data"
     *     )
     * )
     */
    public function spatialDistribution()
    {
        $farmerClusters = DB::select("
            SELECT 
                ST_AsGeoJSON(ST_Centroid(cluster_geom)) as center,
                COUNT(*) as count
            FROM (
                SELECT ST_ClusterKMeans(location, 10) OVER () as cluster_id,
                       ST_Collect(location) as cluster_geom
                FROM farmers
            ) clusters
            GROUP BY cluster_id, cluster_geom
        ");

        $farmSizeDistribution = DB::select("
            SELECT 
                soil_type,
                COUNT(*) as count,
                SUM(size_hectares) as total_area
            FROM farms
            GROUP BY soil_type
        ");

        return response()->json([
            'farmer_clusters' => $farmerClusters,
            'farm_distribution' => $farmSizeDistribution
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/analytics/project-metrics",
     *     summary="Get project performance metrics",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Project metrics data"
     *     )
     * )
     */
    public function projectMetrics()
    {
        $projectProgress = Project::select(
            'id',
            'name',
            'start_date',
            'end_date',
            'budget',
            DB::raw('(SELECT COUNT(*) FROM farmers WHERE ST_Within(location, projects.coverage_area)) as beneficiary_count')
        )->get();

        $metrics = $projectProgress->map(function($project) {
            return [
                'name' => $project->name,
                'progress' => $project->progress_percentage,
                'beneficiaries' => $project->beneficiary_count,
                'duration_months' => $project->duration_months,
                'budget' => $project->budget
            ];
        });

        return response()->json($metrics);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/analytics/weather-trends",
     *     summary="Get weather trends and analysis",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="days",
     *         in="query",
     *         description="Number of days to analyze",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Weather trends data"
     *     )
     * )
     */
    public function weatherTrends(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $trends = WeatherData::select(
            DB::raw('DATE(recorded_at) as date'),
            DB::raw('AVG(temperature) as avg_temp'),
            DB::raw('SUM(rainfall) as total_rainfall'),
            DB::raw('AVG(humidity) as avg_humidity')
        )
        ->where('recorded_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        $anomalies = WeatherData::where('recorded_at', '>=', $startDate)
            ->where(function($query) {
                $query->where('temperature', '>', 35)
                    ->orWhere('rainfall', '>', 50)
                    ->orWhere('wind_speed', '>', 20);
            })
            ->get();

        return response()->json([
            'trends' => $trends,
            'anomalies' => $anomalies
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/analytics/farm-productivity",
     *     summary="Get farm productivity analysis",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Farm productivity data"
     *     )
     * )
     */
    public function farmProductivity()
    {
        $productivity = Farm::select(
            'soil_type',
            DB::raw('COUNT(*) as farm_count'),
            DB::raw('AVG(size_hectares) as avg_size'),
            DB::raw('SUM(size_hectares) as total_area')
        )
        ->groupBy('soil_type')
        ->get();

        $waterSourceAnalysis = Farm::select(
            'water_source',
            DB::raw('COUNT(*) as count'),
            DB::raw('AVG(size_hectares) as avg_farm_size')
        )
        ->groupBy('water_source')
        ->get();

        return response()->json([
            'soil_type_analysis' => $productivity,
            'water_source_analysis' => $waterSourceAnalysis
        ]);
    }
}
