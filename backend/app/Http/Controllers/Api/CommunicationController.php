<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommunicationRequest;
use App\Http\Requests\UpdateCommunicationRequest;
use App\Models\Communication;
use App\Models\Farmer;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class CommunicationController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Display a listing of communications with optional filtering and pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Communication::query()
                ->with('recipient')
                ->when($request->type, fn($q, $type) => $q->where('type', $type))
                ->when($request->status, fn($q, $status) => $q->where('status', $status))
                ->when($request->recipient_id, fn($q, $id) => $q->where('recipient_id', $id))
                ->when($request->start_date, fn($q, $date) => $q->where('sent_at', '>=', $date))
                ->when($request->end_date, fn($q, $date) => $q->where('sent_at', '<=', $date));

            $communications = $query->orderBy('sent_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json($communications);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving communications',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created communication.
     *
     * @param StoreCommunicationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCommunicationRequest $request)
    {
        try {
            DB::beginTransaction();

            $communication = Communication::create($request->validated());

            if ($communication->type === 'sms') {
                $result = $this->smsService->sendSms(
                    $communication->recipient->phone_number,
                    $communication->message
                );

                $communication->update([
                    'status' => $result['status'],
                    'sent_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Communication created successfully',
                'data' => $communication->load('recipient')
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating communication',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified communication.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $communication = Communication::with('recipient')->findOrFail($id);

            return response()->json($communication);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Communication not found',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified communication.
     *
     * @param UpdateCommunicationRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCommunicationRequest $request, string $id)
    {
        try {
            $communication = Communication::findOrFail($id);
            $communication->update($request->validated());

            return response()->json([
                'message' => 'Communication updated successfully',
                'data' => $communication->load('recipient')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating communication',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified communication.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $communication = Communication::findOrFail($id);
            $communication->delete();

            return response()->json([
                'message' => 'Communication deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting communication',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Send SMS to farmers in a specific region.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToRegion(Request $request)
    {
        $request->validate([
            'region' => 'required|array',
            'region.type' => 'required|string|in:Polygon',
            'region.coordinates' => 'required|array',
            'message' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $farmers = Farmer::whereRaw('ST_Contains(
                    ST_SetSRID(ST_GeomFromGeoJSON(?), 4326),
                    location_coordinates
                )', [json_encode($request->region)])
                ->whereNotNull('phone_number')
                ->get();

            $phoneNumbers = $farmers->pluck('phone_number')->toArray();
            $result = $this->smsService->sendBulkSms($phoneNumbers, $request->message);

            // Record communications
            foreach ($farmers as $farmer) {
                Communication::create([
                    'type' => 'sms',
                    'recipient_id' => $farmer->user_id,
                    'message' => $request->message,
                    'status' => 'sent',
                    'sent_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Regional SMS broadcast completed',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error sending regional SMS',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send weather alerts to affected farmers.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendWeatherAlerts(Request $request)
    {
        $request->validate([
            'alert_type' => 'required|string|in:rain,temperature,wind',
            'severity' => 'required|string|in:warning,alert,emergency',
            'region' => 'required|array',
            'message' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $farmers = Farmer::whereRaw('ST_Contains(
                    ST_SetSRID(ST_GeomFromGeoJSON(?), 4326),
                    location_coordinates
                )', [json_encode($request->region)])
                ->whereNotNull('phone_number')
                ->get();

            $phoneNumbers = $farmers->pluck('phone_number')->toArray();
            $result = $this->smsService->sendBulkSms($phoneNumbers, $request->message);

            // Record alerts
            foreach ($farmers as $farmer) {
                Communication::create([
                    'type' => 'alert',
                    'recipient_id' => $farmer->user_id,
                    'message' => $request->message,
                    'status' => 'sent',
                    'sent_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Weather alerts sent successfully',
                'data' => [
                    'alert_type' => $request->alert_type,
                    'severity' => $request->severity,
                    'recipients' => count($phoneNumbers),
                    'result' => $result
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error sending weather alerts',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get communication statistics.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        try {
            $stats = [
                'overall' => [
                    'total' => Communication::count(),
                    'by_type' => Communication::groupBy('type')
                        ->select('type', DB::raw('count(*) as count'))
                        ->get(),
                    'by_status' => Communication::groupBy('status')
                        ->select('status', DB::raw('count(*) as count'))
                        ->get(),
                ],
                'recent' => [
                    'today' => Communication::whereDate('sent_at', Carbon::today())->count(),
                    'this_week' => Communication::whereBetween('sent_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ])->count(),
                    'this_month' => Communication::whereBetween('sent_at', [
                        Carbon::now()->startOfMonth(),
                        Carbon::now()->endOfMonth()
                    ])->count(),
                ],
                'daily_breakdown' => Communication::whereBetween('sent_at', [
                        Carbon::now()->subDays(30),
                        Carbon::now()
                    ])
                    ->groupBy(DB::raw('DATE(sent_at)'))
                    ->select(
                        DB::raw('DATE(sent_at) as date'),
                        DB::raw('count(*) as total'),
                        DB::raw('sum(case when status = "delivered" then 1 else 0 end) as delivered'),
                        DB::raw('sum(case when status = "failed" then 1 else 0 end) as failed')
                    )
                    ->orderBy('date')
                    ->get()
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving communication statistics',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
