<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FarmerController;
use App\Http\Controllers\Api\FarmController;
use App\Http\Controllers\Api\CropController;
use App\Http\Controllers\Api\LivestockController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectMetricController;
use App\Http\Controllers\Api\WeatherDataController;
use App\Http\Controllers\Api\CommunicationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DataManagementController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\MonitoringController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Version 1 API Routes
Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
        });
    });

    // Protected Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        // User Management (Admin only)
        Route::middleware(['check.role:admin'])->prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('{id}', [UserController::class, 'show']);
            Route::put('{id}', [UserController::class, 'update']);
            Route::delete('{id}', [UserController::class, 'destroy']);
        });

        // Farmers
        Route::prefix('farmers')->group(function () {
            Route::get('/', [FarmerController::class, 'index']);
            Route::post('/', [FarmerController::class, 'store']);
            Route::get('{id}', [FarmerController::class, 'show']);
            Route::put('{id}', [FarmerController::class, 'update']);
            Route::delete('{id}', [FarmerController::class, 'destroy']);
            Route::get('nearby/{lat}/{lng}/{distance}', [FarmerController::class, 'nearby']);
            Route::post('in-area', [FarmerController::class, 'inArea']);
            Route::get('statistics', [FarmerController::class, 'statistics']);
        });

        // Farms
        Route::prefix('farms')->group(function () {
            Route::get('/', [FarmController::class, 'index']);
            Route::post('/', [FarmController::class, 'store']);
            Route::get('{id}', [FarmController::class, 'show']);
            Route::put('{id}', [FarmController::class, 'update']);
            Route::delete('{id}', [FarmController::class, 'destroy']);
            Route::post('in-area', [FarmController::class, 'inArea']);
            Route::get('statistics', [FarmController::class, 'statistics']);
            Route::post('{id}/calculate-area', [FarmController::class, 'calculateArea']);
        });

        // Crops
        Route::prefix('crops')->group(function () {
            Route::get('/', [CropController::class, 'index']);
            Route::post('/', [CropController::class, 'store']);
            Route::get('{id}', [CropController::class, 'show']);
            Route::put('{id}', [CropController::class, 'update']);
            Route::delete('{id}', [CropController::class, 'destroy']);
            Route::get('statistics', [CropController::class, 'statistics']);
        });

        // Projects
        Route::prefix('projects')->group(function () {
            Route::get('/', [ProjectController::class, 'index']);
            Route::post('/', [ProjectController::class, 'store']);
            Route::get('{id}', [ProjectController::class, 'show']);
            Route::put('{id}', [ProjectController::class, 'update']);
            Route::delete('{id}', [ProjectController::class, 'destroy']);
            Route::post('in-area', [ProjectController::class, 'inArea']);
            Route::get('{id}/farmers', [ProjectController::class, 'getFarmers']);
            Route::get('{id}/farms', [ProjectController::class, 'getFarms']);
            Route::get('statistics', [ProjectController::class, 'statistics']);
        });

        // Project Metrics
        Route::prefix('project-metrics')->group(function () {
            Route::get('/', [ProjectMetricController::class, 'index']);
            Route::post('/', [ProjectMetricController::class, 'store']);
            Route::get('{id}', [ProjectMetricController::class, 'show']);
            Route::put('{id}', [ProjectMetricController::class, 'update']);
            Route::delete('{id}', [ProjectMetricController::class, 'destroy']);
            Route::get('project/{projectId}/summary', [ProjectMetricController::class, 'projectSummary']);
        });

        // Weather Data
        Route::prefix('weather')->group(function () {
            Route::get('/', [WeatherDataController::class, 'index']);
            Route::post('/', [WeatherDataController::class, 'store']);
            Route::get('{id}', [WeatherDataController::class, 'show']);
            Route::put('{id}', [WeatherDataController::class, 'update']);
            Route::delete('{id}', [WeatherDataController::class, 'destroy']);
            Route::post('in-region', [WeatherDataController::class, 'inRegion']);
            Route::get('alerts', [WeatherDataController::class, 'alerts']);
            Route::post('regional-average', [WeatherDataController::class, 'regionalAverage']);
        });

        // Communications
        Route::prefix('communications')->group(function () {
            Route::get('/', [CommunicationController::class, 'index']);
            Route::post('/', [CommunicationController::class, 'store']);
            Route::get('{id}', [CommunicationController::class, 'show']);
            Route::put('{id}', [CommunicationController::class, 'update']);
            Route::delete('{id}', [CommunicationController::class, 'destroy']);
            Route::post('send-sms', [CommunicationController::class, 'sendSms']);
            Route::post('broadcast-region', [CommunicationController::class, 'broadcastToRegion']);
            Route::post('weather-alert', [CommunicationController::class, 'sendWeatherAlert']);
        });

        // Roles
        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::get('{id}', [RoleController::class, 'show']);
            Route::put('{id}', [RoleController::class, 'update']);
            Route::delete('{id}', [RoleController::class, 'destroy']);
        });

        // Data Management
        Route::prefix('data')->group(function () {
            Route::post('import', [DataManagementController::class, 'import']);
            Route::get('export/{type}', [DataManagementController::class, 'export']);
        });

        // Analytics
        Route::prefix('analytics')->group(function () {
            Route::get('dashboard', [AnalyticsController::class, 'dashboard']);
            Route::get('spatial-distribution', [AnalyticsController::class, 'spatialDistribution']);
            Route::get('project-metrics', [AnalyticsController::class, 'projectMetrics']);
            Route::get('weather-trends', [AnalyticsController::class, 'weatherTrends']);
            Route::get('farm-productivity', [AnalyticsController::class, 'farmProductivity']);
        });

        // Monitoring
        Route::prefix('monitoring')->middleware('check.role:admin')->group(function () {
            Route::get('queue', [MonitoringController::class, 'queueStatus']);
            Route::get('sms', [MonitoringController::class, 'smsStats']);
            Route::get('failed-jobs', [MonitoringController::class, 'failedJobs']);
            Route::post('retry-job/{id}', [MonitoringController::class, 'retryJob']);
        });
    });
});
