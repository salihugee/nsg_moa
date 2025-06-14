<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\ProjectMetric;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects with optional filtering and pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Project::query()
                ->with(['metrics'])
                ->when($request->status, fn($q, $status) => $q->where('status', $status))
                ->when($request->search, function($q, $search) {
                    $q->where(function($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                })
                ->when($request->start_date, fn($q, $date) => $q->where('start_date', '>=', $date))
                ->when($request->end_date, fn($q, $date) => $q->where('end_date', '<=', $date))
                ->when($request->min_budget, fn($q, $min) => $q->where('budget', '>=', $min))
                ->when($request->max_budget, fn($q, $max) => $q->where('budget', '<=', $max));

            $projects = $query->paginate($request->per_page ?? 15);

            return response()->json($projects);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving projects',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created project.
     *
     * @param StoreProjectRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProjectRequest $request)
    {
        try {
            DB::beginTransaction();

            $project = Project::create($request->except('metrics'));

            if ($request->has('metrics')) {
                foreach ($request->metrics as $metric) {
                    $project->metrics()->create([
                        'metric_name' => $metric['metric_name'],
                        'target_value' => $metric['target_value'],
                        'current_value' => $metric['current_value'] ?? 0,
                        'last_updated' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Project created successfully',
                'data' => $project->load('metrics')
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating project',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified project.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $project = Project::with(['metrics'])->findOrFail($id);

            return response()->json($project);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Project not found',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified project.
     *
     * @param UpdateProjectRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProjectRequest $request, string $id)
    {
        try {
            $project = Project::findOrFail($id);
            $project->update($request->validated());

            return response()->json([
                'message' => 'Project updated successfully',
                'data' => $project->load('metrics')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating project',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified project.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $project = Project::findOrFail($id);
            $project->metrics()->delete();
            $project->delete();

            return response()->json([
                'message' => 'Project deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting project',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Find projects that intersect with a given area.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function inArea(Request $request)
    {
        $request->validate([
            'area' => 'required|array',
            'area.type' => 'required|string|in:Polygon',
            'area.coordinates' => 'required|array',
        ]);

        try {
            $projects = Project::whereRaw('ST_Intersects(
                    ST_SetSRID(ST_GeomFromGeoJSON(?), 4326),
                    coverage_area
                )', [json_encode($request->area)])
                ->with('metrics')
                ->get();

            return response()->json([
                'count' => $projects->count(),
                'total_budget' => $projects->sum('budget'),
                'projects' => $projects
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error finding projects in area',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get project progress overview.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function progress()
    {
        try {
            $projects = Project::with('metrics')->get();

            $progress = [
                'total_projects' => $projects->count(),
                'total_budget' => $projects->sum('budget'),
                'by_status' => $projects->groupBy('status')
                    ->map(fn($group) => [
                        'count' => $group->count(),
                        'budget' => $group->sum('budget')
                    ]),
                'timeline' => [
                    'upcoming' => $projects->where('start_date', '>', now())->count(),
                    'ongoing' => $projects->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())->count(),
                    'completed' => $projects->where('end_date', '<', now())->count(),
                ],
                'metrics_summary' => ProjectMetric::selectRaw('
                    metric_name,
                    count(*) as count,
                    avg(current_value/target_value * 100) as avg_progress,
                    sum(target_value) as total_target,
                    sum(current_value) as total_current
                ')
                ->groupBy('metric_name')
                ->get()
            ];

            return response()->json($progress);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving project progress',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get project statistics and analytics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $currentYear = date('Y');
            
            $stats = [
                'summary' => [
                    'total_projects' => Project::count(),
                    'active_projects' => Project::where('status', 'ongoing')->count(),
                    'total_budget' => Project::sum('budget'),
                    'avg_duration' => Project::selectRaw('
                        AVG(DATEDIFF(end_date, start_date)) as avg_days
                    ')->first()->avg_days
                ],
                'by_status' => Project::groupBy('status')
                    ->select('status', 
                        DB::raw('count(*) as count'),
                        DB::raw('sum(budget) as total_budget'))
                    ->get(),
                'yearly_breakdown' => Project::selectRaw('
                    YEAR(start_date) as year,
                    count(*) as projects,
                    sum(budget) as total_budget,
                    sum(case when status = "completed" then 1 else 0 end) as completed
                ')
                ->groupBy('year')
                ->orderBy('year')
                ->get(),
                'coverage_analysis' => Project::selectRaw('
                    ST_Area(ST_Union(coverage_area)) as total_area,
                    count(*) as project_count
                ')
                ->first(),
                'metric_performance' => ProjectMetric::selectRaw('
                    metric_name,
                    avg(current_value/target_value * 100) as achievement_rate,
                    count(*) as count
                ')
                ->groupBy('metric_name')
                ->get()
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving project statistics',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
