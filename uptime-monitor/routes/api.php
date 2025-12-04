<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MonitorController;
use App\Http\Controllers\Api\MonitorCheckController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\NotificationChannelController;
use App\Http\Controllers\Api\MonitoringLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('overview', [DashboardController::class, 'overview']);
        Route::get('response-time-stats', [DashboardController::class, 'responseTimeStats']);
        Route::get('uptime-stats', [DashboardController::class, 'uptimeStats']);
        Route::get('incident-history', [DashboardController::class, 'incidentHistory']);
        Route::get('check-history', [DashboardController::class, 'checkHistory']);
        Route::get('ssl-report', [DashboardController::class, 'sslReport']);
    });

    // Monitor routes
    Route::prefix('monitors')->group(function () {
        Route::get('groups', [MonitorController::class, 'groups']);
        Route::get('grouped', [MonitorController::class, 'grouped']);
        Route::post('bulk-action', [MonitorController::class, 'bulkAction']);
    });
    Route::apiResource('monitors', MonitorController::class);
    Route::prefix('monitors/{monitor}')->group(function () {
        Route::post('pause', [MonitorController::class, 'pause']);
        Route::post('resume', [MonitorController::class, 'resume']);
    });

    // Notification channels routes
    Route::apiResource('notification-channels', NotificationChannelController::class);
    Route::post('notification-channels/{notificationChannel}/test', [NotificationChannelController::class, 'test']);

    // Monitor checks routes
    Route::apiResource('monitor-checks', MonitorCheckController::class)->only(['index', 'show']);

    // Incidents routes
    Route::apiResource('incidents', IncidentController::class)->only(['index', 'show', 'update']);

    // Monitoring logs routes
    Route::prefix('logs')->group(function () {
        // Get recent logs across all monitors
        Route::get('recent', [MonitoringLogController::class, 'getRecentLogs']);
        
        // Get available filters
        Route::get('filters', [MonitoringLogController::class, 'getLogFilters']);
        
        // Monitor specific log routes
        Route::prefix('monitor/{monitorId}')->group(function () {
            Route::get('/', [MonitoringLogController::class, 'getMonitorLogs']);
            Route::get('stats', [MonitoringLogController::class, 'getLogStats']);
            Route::get('export', [MonitoringLogController::class, 'exportLogs']);
        });
    });
});

// Heartbeat endpoint for push monitors (public)
Route::post('heartbeat/{heartbeat_key}', function (Request $request, $heartbeatKey) {
    // This will be implemented in the monitoring job
    return response()->json(['success' => true, 'message' => 'Heartbeat received']);
});