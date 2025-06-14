<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectMetricRequest;
use App\Http\Requests\UpdateProjectMetricRequest;
use App\Models\ProjectMetric;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ProjectMetricController extends Controller
{
    /**
     * Display a listing of project metrics with optional filtering and pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = ProjectMetric::query()
                ->with(['project'])
                ->when($request->project_id, fn($q, $projectId) => $q->where('project_id', $projectId))
                ->when($request->metric_name, fn($q, $name) => $q->where('metric_name', $name))
                ->when($request->min_progress, function($q, $minProgress) {
                    $q->whereRaw('(current_value / target_value * 100) >= ?', [$minProgress]);
                })
                ->when($request->max_progress, function($q, $maxProgress) {
                    $q->whereRaw('(current_value / target_value * 100) <= ?', [$maxProgress]);
                });

            $metrics = $query->paginate($request->per_page ?? 15);

            return response()->json($metrics);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving project metrics',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created project metric.
     *
     * @param StoreProjectMetricRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProjectMetricRequest $request)
    {
        try {
            $metric = ProjectMetric::create($request->validated());

            return response()->json([
                'message' => 'Project metric created successfully',
                'data' => $metric->load('project')
            ], Response::HTTP_CREATED);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error creating project metric',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified project metric.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $metric = ProjectMetric::with('project')->findOrFail($id);

            return response()->json($metric);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Project metric not found',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified project metric.
     *
     * @param UpdateProjectMetricRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProjectMetricRequest $request, string $id)
    {
        try {
            $metric = ProjectMetric::findOrFail($id);
            $metric->update($request->validated());

            // Update the last_updated timestamp
            $metric->last_updated = now();
            $metric->save();

            return response()->json([
                'message' => 'Project metric updated successfully',
                'data' => $metric->load('project')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating project metric',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified project metric.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $metric = ProjectMetric::findOrFail($id);
            $metric->delete();

            return response()->json([
                'message' => 'Project metric deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting project metric',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get progress summary for all metrics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function progressSummary()
    {
        try {
            $metrics = ProjectMetric::selectRaw('
                    metric_name,
                    count(*) as total_count,
                    avg(current_value/target_value * 100) as avg_progress,
                    min(current_value/target_value * 100) as min_progress,
                    max(current_value/target_value * 100) as max_progress,
                    sum(target_value) as total_target,
                    sum(current_value) as total_current
                ')
                ->groupBy('metric_name')
                ->get()
                ->map(function($metric) {
                    $metric->avg_progress = round($metric->avg_progress, 2);
                    $metric->min_progress = round($metric->min_progress, 2);
                    $metric->max_progress = round($metric->max_progress, 2);
                    return $metric;
                });

            return response()->json([
                'metrics' => $metrics,
                'overall_progress' => [
                    'total_metrics' => ProjectMetric::count(),
                    'avg_completion' => round(ProjectMetric::avg(DB::raw('current_value/target_value * 100')), 2),
                    'recently_updated' => ProjectMetric::where('last_updated', '>=', now()->subDays(7))->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving progress summary',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Track metric changes over time.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function trendAnalysis(Request $request)
    {
        $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
            'metric_name' => 'nullable|string'
        ]);

        $days = $request->days ?? 30;

        try {
            $query = ProjectMetric::selectRaw('
                    DATE(last_updated) as date,
                    metric_name,
                    count(*) as updates,
                    avg(current_value/target_value * 100) as avg_progress
                ')
                ->where('last_updated', '>=', now()->subDays($days))
                ->when($request->metric_name, fn($q, $name) => $q->where('metric_name', $name))
                ->groupBy('date', 'metric_name')
                ->orderBy('date');

            $trends = $query->get()
                ->groupBy('metric_name')
                ->map(function($dates) {
                    return $dates->map(function($metric) {
                        $metric->avg_progress = round($metric->avg_progress, 2);
                        return $metric;
                    });
                });

            return response()->json([
                'period' => "{$days} days",
                'trends' => $trends
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving trend analysis',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
