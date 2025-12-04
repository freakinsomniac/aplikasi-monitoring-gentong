<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Monitor;
use App\Models\MonitorCheck;
use App\Models\Incident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard overview statistics
     */
    public function overview(): JsonResponse
    {
        $user = auth('api')->user();
        
        // Base query for user's monitors
        $monitorsQuery = Monitor::query()
            ->when(!$user->is_admin, function ($q) use ($user) {
                return $q->where('created_by', $user->id);
            });

        // Total monitors
        $totalMonitors = $monitorsQuery->count();
        
        // Monitors by status
        $monitorsByStatus = $monitorsQuery->select('last_status', DB::raw('count(*) as count'))
            ->groupBy('last_status')
            ->get()
            ->pluck('count', 'last_status')
            ->toArray();

        // Active incidents
        $activeIncidents = Incident::whereHas('monitor', function ($q) use ($user) {
                if (!$user->is_admin) {
                    $q->where('created_by', $user->id);
                }
            })
            ->where('resolved', false)
            ->count();

        // Recent checks (last 24h)
        $recentChecks = MonitorCheck::whereHas('monitor', function ($q) use ($user) {
                if (!$user->is_admin) {
                    $q->where('created_by', $user->id);
                }
            })
            ->where('checked_at', '>=', now()->subDay())
            ->count();

        // Average response time (last 24h)
        $avgResponseTime = MonitorCheck::whereHas('monitor', function ($q) use ($user) {
                if (!$user->is_admin) {
                    $q->where('created_by', $user->id);
                }
            })
            ->where('checked_at', '>=', now()->subDay())
            ->where('status', 'up')
            ->avg('latency_ms');

        return response()->json([
            'success' => true,
            'data' => [
                'total_monitors' => $totalMonitors,
                'monitors_by_status' => [
                    'up' => $monitorsByStatus['up'] ?? 0,
                    'down' => $monitorsByStatus['down'] ?? 0,
                    'unknown' => $monitorsByStatus['unknown'] ?? 0,
                ],
                'active_incidents' => $activeIncidents,
                'recent_checks_24h' => $recentChecks,
                'avg_response_time_ms' => $avgResponseTime ? round($avgResponseTime, 2) : null,
            ]
        ]);
    }

    /**
     * Get response time statistics
     */
    public function responseTimeStats(Request $request): JsonResponse
    {
        $period = $request->get('period', '24h'); // 24h, 7d, 30d
        $monitorId = $request->get('monitor_id');
        $user = auth('api')->user();

        // Determine time range
        $startTime = match($period) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => now()->subDay(),
        };

        $query = MonitorCheck::with('monitor:id,name')
            ->where('checked_at', '>=', $startTime)
            ->where('status', 'up')
            ->whereNotNull('latency_ms');

        // Filter by monitor
        if ($monitorId) {
            $query->where('monitor_id', $monitorId);
        }

        // Filter by user's monitors
        $query->whereHas('monitor', function ($q) use ($user) {
            if (!$user->is_admin) {
                $q->where('created_by', $user->id);
            }
        });

        // Group by hour/day based on period
        $groupBy = $period === '24h' ? 'hour' : 'day';
        $timeFormat = $period === '24h' ? 'YYYY-MM-DD HH24:00:00' : 'YYYY-MM-DD';

        $stats = $query->select(
                DB::raw("TO_CHAR(checked_at, '$timeFormat') as period"),
                DB::raw('AVG(latency_ms) as avg_response_time'),
                DB::raw('MIN(latency_ms) as min_response_time'),
                DB::raw('MAX(latency_ms) as max_response_time'),
                DB::raw('COUNT(*) as checks_count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'stats' => $stats
            ]
        ]);
    }

    /**
     * Get uptime statistics
     */
    public function uptimeStats(Request $request): JsonResponse
    {
        $period = $request->get('period', '24h');
        $monitorId = $request->get('monitor_id');
        $user = auth('api')->user();

        $startTime = match($period) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => now()->subDay(),
        };

        $query = MonitorCheck::with('monitor:id,name')
            ->where('checked_at', '>=', $startTime);

        if ($monitorId) {
            $query->where('monitor_id', $monitorId);
        }

        $query->whereHas('monitor', function ($q) use ($user) {
            if (!$user->is_admin) {
                $q->where('created_by', $user->id);
            }
        });

        $timeFormat = $period === '24h' ? 'YYYY-MM-DD HH24:00:00' : 'YYYY-MM-DD';

        $stats = $query->select(
                DB::raw("TO_CHAR(checked_at, '$timeFormat') as period"),
                DB::raw('COUNT(*) as total_checks'),
                DB::raw("COUNT(CASE WHEN status = 'up' THEN 1 END) as up_checks"),
                DB::raw("COUNT(CASE WHEN status = 'down' THEN 1 END) as down_checks"),
                DB::raw("ROUND((COUNT(CASE WHEN status = 'up' THEN 1 END)::numeric / COUNT(*)) * 100, 2) as uptime_percentage")
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'stats' => $stats
            ]
        ]);
    }

    /**
     * Get incident history
     */
    public function incidentHistory(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        
        $query = Incident::with(['monitor:id,name'])
            ->whereHas('monitor', function ($q) use ($user) {
                if (!$user->is_admin) {
                    $q->where('created_by', $user->id);
                }
            });

        // Filter by monitor
        if ($request->has('monitor_id')) {
            $query->where('monitor_id', $request->monitor_id);
        }

        // Filter by resolved status
        if ($request->has('resolved')) {
            $query->where('resolved', $request->boolean('resolved'));
        }

        $incidents = $query->latest('started_at')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $incidents
        ]);
    }

    /**
     * Get check history
     */
    public function checkHistory(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        $monitorId = $request->get('monitor_id');

        if (!$monitorId) {
            return response()->json([
                'success' => false,
                'message' => 'Monitor ID is required'
            ], 422);
        }

        // Verify monitor access
        $monitor = Monitor::find($monitorId);
        if (!$monitor || (!$user->is_admin && $monitor->created_by !== $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Monitor not found or access denied'
            ], 404);
        }

        $query = MonitorCheck::where('monitor_id', $monitorId);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('checked_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->where('checked_at', '<=', $request->end_date);
        }

        $checks = $query->latest('checked_at')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $checks
        ]);
    }

    /**
     * Get SSL certificate expiry report
     */
    public function sslReport(): JsonResponse
    {
        $user = auth('api')->user();
        
        // Get HTTPS monitors
        $httpsMonitors = Monitor::where('type', 'https')
            ->when(!$user->is_admin, function ($q) use ($user) {
                return $q->where('created_by', $user->id);
            })
            ->with(['checks' => function ($query) {
                $query->latest()
                    ->limit(1)
                    ->whereNotNull('meta');
            }])
            ->get();

        $sslData = [];
        
        foreach ($httpsMonitors as $monitor) {
            $lastCheck = $monitor->checks->first();
            
            if ($lastCheck && isset($lastCheck->meta['ssl_expiry'])) {
                $expiryDate = Carbon::parse($lastCheck->meta['ssl_expiry']);
                $daysUntilExpiry = now()->diffInDays($expiryDate, false);
                
                $sslData[] = [
                    'monitor_id' => $monitor->id,
                    'monitor_name' => $monitor->name,
                    'target' => $monitor->target,
                    'ssl_expiry_date' => $expiryDate->toDateTimeString(),
                    'days_until_expiry' => $daysUntilExpiry,
                    'status' => $daysUntilExpiry < 0 ? 'expired' : ($daysUntilExpiry < 30 ? 'expiring_soon' : 'valid')
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $sslData
        ]);
    }
}
