<?php

namespace App\Console\Commands;

use App\Models\Monitor;
use App\Models\MonitoringLog;
use Illuminate\Console\Command;

class TestMonitoringLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:test {--monitor-id= : Test specific monitor only} {--count=10 : Number of test logs to create}';

    /**
     * The console command description.
     */
    protected $description = 'Create test monitoring logs for development/testing purposes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = (int) $this->option('count');
        $monitorId = $this->option('monitor-id');

        if ($monitorId) {
            $monitors = Monitor::where('id', $monitorId)->get();
            if ($monitors->isEmpty()) {
                $this->error("Monitor with ID {$monitorId} not found.");
                return 1;
            }
        } else {
            $monitors = Monitor::all();
        }

        if ($monitors->isEmpty()) {
            $this->error('No monitors found. Create some monitors first.');
            return 1;
        }

        $this->info("Creating {$count} test logs for " . $monitors->count() . " monitor(s)...");

        $eventTypes = ['check_start', 'check_complete', 'check_failed', 'status_change', 'check_skipped'];
        $statuses = ['up', 'down', null];
        
        $bar = $this->output->createProgressBar($count * $monitors->count());
        $bar->start();

        foreach ($monitors as $monitor) {
            for ($i = 0; $i < $count; $i++) {
                $eventType = $eventTypes[array_rand($eventTypes)];
                $status = $statuses[array_rand($statuses)];
                
                // Generate realistic data based on event type
                $responseTime = null;
                $errorMessage = null;
                $logData = [
                    'monitor_type' => $monitor->type,
                    'monitor_url' => $monitor->target,
                    'test_data' => true,
                    'iteration' => $i + 1,
                    'execution_time_ms' => rand(50, 2000),
                ];

                if ($eventType === 'check_complete') {
                    $responseTime = rand(50, 2000) / 10; // 5ms to 200ms
                    $logData['http_status'] = rand(0, 10) > 1 ? 200 : 500; // 90% success rate
                    $logData['response_size'] = rand(1000, 50000);
                } elseif ($eventType === 'check_failed') {
                    $status = 'down';
                    $errorMessages = [
                        'Connection timeout',
                        'DNS resolution failed',
                        'SSL certificate error',
                        'HTTP 500 Internal Server Error',
                        'Connection refused',
                        'Network unreachable'
                    ];
                    $errorMessage = $errorMessages[array_rand($errorMessages)];
                    $logData['error_details'] = [
                        'error_code' => rand(1, 100),
                        'timeout_ms' => $monitor->timeout_ms ?? 30000,
                    ];
                } elseif ($eventType === 'status_change') {
                    $previousStatus = $status === 'up' ? 'down' : 'up';
                    $logData['previous_status'] = $previousStatus;
                    $logData['new_status'] = $status;
                    $logData['consecutive_failures'] = $status === 'down' ? rand(1, 5) : 0;
                }

                // Create log entry with timestamp spread over last 24 hours
                $loggedAt = now()->subMinutes(rand(1, 1440)); // Random time in last 24 hours
                
                MonitoringLog::create([
                    'monitor_id' => $monitor->id,
                    'event_type' => $eventType,
                    'status' => $status,
                    'response_time' => $responseTime,
                    'error_message' => $errorMessage,
                    'log_data' => $logData,
                    'logged_at' => $loggedAt,
                ]);

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Test logs created successfully!');

        // Show summary
        $totalLogs = MonitoringLog::count();
        $this->table(['Metric', 'Count'], [
            ['Total Logs in System', $totalLogs],
            ['Logs Created This Session', $count * $monitors->count()],
            ['Monitors Affected', $monitors->count()],
            ['Event Types Used', implode(', ', $eventTypes)],
        ]);

        $this->info('You can now test the logging API endpoints and frontend interface.');
        $this->comment('API endpoints available:');
        $this->line('- GET /api/logs/recent - Get recent logs across all monitors');
        $this->line('- GET /api/logs/monitor/{id} - Get logs for specific monitor');
        $this->line('- GET /api/logs/monitor/{id}/stats - Get log statistics');
        $this->line('- GET /api/logs/monitor/{id}/export - Export logs as JSON');
        $this->line('- GET /api/logs/filters - Get available filter options');

        return 0;
    }
}