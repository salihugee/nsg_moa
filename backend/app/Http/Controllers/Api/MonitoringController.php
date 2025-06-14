<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use App\Models\Communication;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/monitoring/queue",
     *     summary="Get queue status",
     *     tags={"Monitoring"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Queue status information"
     *     )
     * )
     */
    public function queueStatus()
    {
        $jobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $pendingSms = DB::table('jobs')
            ->where('queue', 'sms')
            ->count();

        return response()->json([
            'total_jobs' => $jobs,
            'failed_jobs' => $failedJobs,
            'pending_sms' => $pendingSms,
            'queues' => [
                'sms' => $pendingSms,
                'default' => $jobs - $pendingSms
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/monitoring/sms",
     *     summary="Get SMS statistics",
     *     tags={"Monitoring"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="SMS statistics"
     *     )
     * )
     */
    public function smsStats()
    {
        $today = Carbon::today();
        $lastWeek = Carbon::now()->subWeek();

        $stats = [
            'today' => [
                'total' => Communication::whereDate('created_at', $today)->count(),
                'success' => Communication::whereDate('created_at', $today)
                    ->where('status', 'delivered')
                    ->count(),
                'failed' => Communication::whereDate('created_at', $today)
                    ->where('status', 'failed')
                    ->count()
            ],
            'week' => [
                'total' => Communication::where('created_at', '>=', $lastWeek)->count(),
                'success' => Communication::where('created_at', '>=', $lastWeek)
                    ->where('status', 'delivered')
                    ->count(),
                'failed' => Communication::where('created_at', '>=', $lastWeek)
                    ->where('status', 'failed')
                    ->count()
            ],
            'by_type' => Communication::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get()
        ];

        return response()->json($stats);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/monitoring/failed-jobs",
     *     summary="Get failed jobs",
     *     tags={"Monitoring"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Failed jobs list"
     *     )
     * )
     */
    public function failedJobs()
    {
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->paginate(10);

        return response()->json($failedJobs);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/monitoring/retry-job/{id}",
     *     summary="Retry a failed job",
     *     tags={"Monitoring"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job retry status"
     *     )
     * )
     */
    public function retryJob($id)
    {
        try {
            Artisan::call('queue:retry', ['id' => $id]);
            return response()->json(['message' => 'Job queued for retry']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retry job'], 500);
        }
    }
}
