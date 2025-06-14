<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWeatherDataRequest;
use App\Http\Requests\UpdateWeatherDataRequest;
use App\Models\WeatherData;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class WeatherDataController extends Controller
{
    /**
     * Display a listing of weather data with optional filtering and pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = WeatherData::query()
                ->when($request->start_date, fn($q, $date) => $q->where('recorded_at', '>=', $date))
                ->when($request->end_date, fn($q, $date) => $q->where('recorded_at', '<=', $date))
                ->when($request->min_temp, fn($q, $temp) => $q->where('temperature', '>=', $temp))
                ->when($request->max_temp, fn($q, $temp) => $q->where('temperature', '<=', $temp))
                ->when($request->min_rainfall, fn($q, $rain) => $q->where('rainfall', '>=', $rain))
                ->when($request->max_rainfall, fn($q, $rain) => $q->where('rainfall', '<=', $rain));

            $weatherData = $query->orderBy('recorded_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json($weatherData);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving weather data',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store new weather data.
     *
     * @param StoreWeatherDataRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreWeatherDataRequest $request)
    {
        try {
            $weatherData = WeatherData::create($request->validated());

            return response()->json([
                'message' => 'Weather data recorded successfully',
                'data' => $weatherData
            ], Response::HTTP_CREATED);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error recording weather data',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display specific weather data record.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $weatherData = WeatherData::findOrFail($id);

            return response()->json($weatherData);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Weather data record not found',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update weather data record.
     *
     * @param UpdateWeatherDataRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateWeatherDataRequest $request, string $id)
    {
        try {
            $weatherData = WeatherData::findOrFail($id);
            $weatherData->update($request->validated());

            return response()->json([
                'message' => 'Weather data updated successfully',
                'data' => $weatherData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating weather data',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove weather data record.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $weatherData = WeatherData::findOrFail($id);
            $weatherData->delete();

            return response()->json([
                'message' => 'Weather data deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting weather data',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get weather data for a specific region.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forRegion(Request $request)
    {
        $request->validate([
            'region' => 'required|array',
            'region.type' => 'required|string|in:Polygon',
            'region.coordinates' => 'required|array',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $weatherData = WeatherData::whereRaw('ST_Contains(
                    ST_SetSRID(ST_GeomFromGeoJSON(?), 4326),
                    location
                )', [json_encode($request->region)])
                ->whereBetween('recorded_at', [$request->start_date, $request->end_date])
                ->get();

            $analysis = [
                'period' => [
                    'start' => $request->start_date,
                    'end' => $request->end_date
                ],
                'summary' => [
                    'avg_temperature' => $weatherData->avg('temperature'),
                    'total_rainfall' => $weatherData->sum('rainfall'),
                    'avg_humidity' => $weatherData->avg('humidity'),
                    'avg_wind_speed' => $weatherData->avg('wind_speed'),
                ],
                'daily_data' => $weatherData->groupBy(function($item) {
                    return Carbon::parse($item->recorded_at)->format('Y-m-d');
                })->map(function($group) {
                    return [
                        'avg_temperature' => $group->avg('temperature'),
                        'total_rainfall' => $group->sum('rainfall'),
                        'avg_humidity' => $group->avg('humidity'),
                        'avg_wind_speed' => $group->avg('wind_speed'),
                        'readings_count' => $group->count()
                    ];
                })
            ];

            return response()->json($analysis);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving regional weather data',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get weather alerts based on thresholds.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function alerts()
    {
        try {
            $today = Carbon::today();
            $alerts = [];

            // High temperature alerts (above 35°C)
            $highTempLocations = WeatherData::where('temperature', '>', 35)
                ->where('recorded_at', '>=', $today)
                ->get();

            if ($highTempLocations->count() > 0) {
                $alerts['high_temperature'] = [
                    'level' => 'warning',
                    'message' => 'High temperature detected in some areas',
                    'locations' => $highTempLocations->map(fn($data) => [
                        'location' => $data->location,
                        'temperature' => $data->temperature,
                        'recorded_at' => $data->recorded_at
                    ])
                ];
            }

            // Heavy rainfall alerts (above 50mm)
            $heavyRainLocations = WeatherData::where('rainfall', '>', 50)
                ->where('recorded_at', '>=', $today)
                ->get();

            if ($heavyRainLocations->count() > 0) {
                $alerts['heavy_rainfall'] = [
                    'level' => 'warning',
                    'message' => 'Heavy rainfall detected in some areas',
                    'locations' => $heavyRainLocations->map(fn($data) => [
                        'location' => $data->location,
                        'rainfall' => $data->rainfall,
                        'recorded_at' => $data->recorded_at
                    ])
                ];
            }

            // Strong wind alerts (above 50 km/h)
            $strongWindLocations = WeatherData::where('wind_speed', '>', 50)
                ->where('recorded_at', '>=', $today)
                ->get();

            if ($strongWindLocations->count() > 0) {
                $alerts['strong_winds'] = [
                    'level' => 'warning',
                    'message' => 'Strong winds detected in some areas',
                    'locations' => $strongWindLocations->map(fn($data) => [
                        'location' => $data->location,
                        'wind_speed' => $data->wind_speed,
                        'recorded_at' => $data->recorded_at
                    ])
                ];
            }

            return response()->json([
                'date' => $today->toDateString(),
                'alerts' => $alerts,
                'alert_count' => count($alerts)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating weather alerts',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get weather statistics and analytics.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $stats = [
                'period' => [
                    'start' => $request->start_date,
                    'end' => $request->end_date
                ],
                'temperature' => [
                    'average' => WeatherData::whereBetween('recorded_at', [$request->start_date, $request->end_date])
                        ->avg('temperature'),
                    'min' => WeatherData::whereBetween('recorded_at', [$request->start_date, $request->end_date])
                        ->min('temperature'),
                    'max' => WeatherData::whereBetween('recorded_at', [$request->start_date, $request->end_date])
                        ->max('temperature')
                ],
                'rainfall' => [
                    'total' => WeatherData::whereBetween('recorded_at', [$request->start_date, $request->end_date])
                        ->sum('rainfall'),
                    'rainy_days' => WeatherData::whereBetween('recorded_at', [$request->start_date, $request->end_date])
                        ->where('rainfall', '>', 0)
                        ->distinct(DB::raw('DATE(recorded_at)'))
                        ->count()
                ],
                'humidity' => [
                    'average' => WeatherData::whereBetween('recorded_at', [$request->start_date, $request->end_date])
                        ->avg('humidity')
                ],
                'wind_speed' => [
                    'average' => WeatherData::whereBetween('recorded_at', [$request->start_date, $request->end_date])
                        ->avg('wind_speed'),
                    'max' => WeatherData::whereBetween('recorded_at', [$request->start_date, $request->end_date])
                        ->max('wind_speed')
                ],
                'daily_averages' => WeatherData::whereBetween('recorded_at', [$request->start_date, $request->end_date])
                    ->selectRaw('
                        DATE(recorded_at) as date,
                        AVG(temperature) as avg_temperature,
                        SUM(rainfall) as total_rainfall,
                        AVG(humidity) as avg_humidity,
                        AVG(wind_speed) as avg_wind_speed,
                        COUNT(*) as readings_count
                    ')
                    ->groupBy(DB::raw('DATE(recorded_at)'))
                    ->orderBy('date')
                    ->get()
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving weather statistics',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
