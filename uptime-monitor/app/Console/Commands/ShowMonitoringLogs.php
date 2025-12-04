<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShowMonitoringLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:monitoring {--limit=10 : Number of logs to show} {--monitor= : Filter by monitor ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show monitoring logs in JSON format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $monitorId = $this->option('monitor');

        $query = DB::table('monitoring_logs')
            ->orderBy('id', 'desc')
            ->limit($limit);

        if ($monitorId) {
            $query->where('monitor_id', $monitorId);
        }

        $logs = $query->get();

        if ($logs->isEmpty()) {
            $this->info('No monitoring logs found.');
            return 0;
        }

        $this->info("Showing latest {$logs->count()} monitoring logs:");
        $this->line('');

        foreach ($logs as $log) {
            $this->line("🕐 [{$log->logged_at}] Monitor #{$log->monitor_id}");
            $this->line("📊 Event: {$log->event_type} | Status: {$log->status}");
            
            if ($log->response_time) {
                $this->line("⏱️  Response Time: {$log->response_time}ms");
            }
            
            if ($log->error_message) {
                $this->line("❌ Error: {$log->error_message}");
            }

            // Pretty print JSON data
            $logData = json_decode($log->log_data, true);
            $this->line("📋 JSON Data:");
            $this->line(json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->line('----------------------------------------');
        }

        return 0;
    }
}
