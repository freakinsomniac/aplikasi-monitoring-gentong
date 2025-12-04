<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MonitoringLog;
use App\Models\Monitor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MonitoringLogController extends Controller
{
    /**
     * Get logs for a specific monitor
     */
    public function getMonitorLogs(Request $request, $monitorId): JsonResponse
    {
        $request->validate([
            'per_page' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
            'event_type' => 'string|nullable',
            'status' => 'string|nullable',
            'hours' => 'integer|min:1|max:168', // max 7 days
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable|after_or_equal:start_date',
        ]);

        $monitor = Monitor::findOrFail($monitorId);
        
        $query = MonitoringLog::where('monitor_id', $monitorId)
            ->orderBy('logged_at', 'desc');

        // Apply filters
        if ($request->filled('event_type')) {
            $query->byEventType($request->event_type);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('hours')) {
            $query->recent($request->hours);
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->inDateRange($request->start_date, $request->end_date);
        } else {
            // Default to last 24 hours
            $query->recent(24);
        }

        $logs = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => [
                'monitor' => [
                    'id' => $monitor->id,
                    'name' => $monitor->name,
                    'target' => $monitor->target,
                    'type' => $monitor->type,
                ],
                'logs' => $logs->items(),
                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                    'from' => $logs->firstItem(),
                    'to' => $logs->lastItem(),
                ]
            ]
        ]);
    }

    /**
     * Get recent logs across all monitors
     */
    public function getRecentLogs(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
            'event_type' => 'string|nullable',
            'status' => 'string|nullable',
            'hours' => 'integer|min:1|max:168',
            'monitor_id' => 'integer|exists:monitors,id|nullable',
        ]);

        $query = MonitoringLog::with('monitor:id,name,target,type')
            ->orderBy('logged_at', 'desc');

        // Apply filters
        if ($request->filled('monitor_id')) {
            $query->where('monitor_id', $request->monitor_id);
        }

        if ($request->filled('event_type')) {
            $query->byEventType($request->event_type);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('hours')) {
            $query->recent($request->hours);
        } else {
            $query->recent(24); // Default to last 24 hours
        }

        $logs = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get log statistics for a monitor
     */
    public function getLogStats(Request $request, $monitorId): JsonResponse
    {
        $request->validate([
            'hours' => 'integer|min:1|max:168',
        ]);

        $hours = $request->get('hours', 24);
        $monitor = Monitor::findOrFail($monitorId);

        $stats = [
            'total_logs' => MonitoringLog::where('monitor_id', $monitorId)->recent($hours)->count(),
            'by_event_type' => MonitoringLog::where('monitor_id', $monitorId)
                ->recent($hours)
                ->selectRaw('event_type, COUNT(*) as count')
                ->groupBy('event_type')
                ->pluck('count', 'event_type'),
            'by_status' => MonitoringLog::where('monitor_id', $monitorId)
                ->recent($hours)
                ->whereNotNull('status')
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'avg_response_time' => MonitoringLog::where('monitor_id', $monitorId)
                ->recent($hours)
                ->whereNotNull('response_time')
                ->avg('response_time'),
            'error_count' => MonitoringLog::where('monitor_id', $monitorId)
                ->recent($hours)
                ->whereNotNull('error_message')
                ->count(),
            'period' => [
                'hours' => $hours,
                'start' => now()->subHours($hours)->toISOString(),
                'end' => now()->toISOString(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'monitor' => [
                    'id' => $monitor->id,
                    'name' => $monitor->name,
                    'target' => $monitor->target,
                ],
                'stats' => $stats
            ]
        ]);
    }

    /**
     * Get available event types and statuses for filtering
     */
    public function getLogFilters(): JsonResponse
    {
        $eventTypes = MonitoringLog::select('event_type')
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type');

        $statuses = MonitoringLog::select('status')
            ->whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        return response()->json([
            'success' => true,
            'data' => [
                'event_types' => $eventTypes,
                'statuses' => $statuses,
            ]
        ]);
    }

    /**
     * Export logs as JSON
     */
    public function exportLogs(Request $request, $monitorId): JsonResponse
    {
        $request->validate([
            'event_type' => 'string|nullable',
            'status' => 'string|nullable',
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable|after_or_equal:start_date',
            'limit' => 'integer|min:1|max:1000',
        ]);

        $monitor = Monitor::findOrFail($monitorId);
        
        $query = MonitoringLog::where('monitor_id', $monitorId)
            ->orderBy('logged_at', 'desc');

        // Apply filters
        if ($request->filled('event_type')) {
            $query->byEventType($request->event_type);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->inDateRange($request->start_date, $request->end_date);
        } else {
            $query->recent(24); // Default to last 24 hours
        }

        $limit = $request->get('limit', 500);
        $logs = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'monitor' => [
                    'id' => $monitor->id,
                    'name' => $monitor->name,
                    'target' => $monitor->target,
                    'type' => $monitor->type,
                ],
                'export_info' => [
                    'total_records' => $logs->count(),
                    'limit_applied' => $limit,
                    'exported_at' => now()->toISOString(),
                    'filters' => $request->only(['event_type', 'status', 'start_date', 'end_date'])
                ],
                'logs' => $logs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'event_type' => $log->event_type,
                        'status' => $log->status,
                        'response_time' => $log->response_time,
                        'error_message' => $log->error_message,
                        'logged_at' => $log->logged_at->toISOString(),
                        'severity' => $log->severity,
                        'log_data' => $log->formatted_log_data,
                    ];
                })
            ]
        ], 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="monitor_' . $monitorId . '_logs_' . now()->format('Y-m-d_H-i-s') . '.json"'
        ]);
    }
}