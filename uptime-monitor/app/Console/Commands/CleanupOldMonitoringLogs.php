<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupOldMonitoringLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:cleanup {--days=30 : Number of days to retain logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete monitoring logs older than specified days (default: 30 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        
        $this->info("Starting cleanup of monitoring logs older than {$days} days...");
        
        $cutoffDate = Carbon::now()->subDays($days);
        
        try {
            // Delete old monitoring_logs
            $deletedCount = DB::table('monitoring_logs')
                ->where('created_at', '<', $cutoffDate)
                ->delete();
            
            $this->info("✓ Deleted {$deletedCount} monitoring log records older than {$cutoffDate->toDateTimeString()}");
            
            // Also cleanup old monitor_checks if exists
            $deletedChecks = DB::table('monitor_checks')
                ->where('created_at', '<', $cutoffDate)
                ->delete();
            
            $this->info("✓ Deleted {$deletedChecks} monitor check records older than {$cutoffDate->toDateTimeString()}");
            
            // Cleanup old monitor_metrics if exists
            $deletedMetrics = DB::table('monitor_metrics')
                ->where('created_at', '<', $cutoffDate)
                ->delete();
            
            $this->info("✓ Deleted {$deletedMetrics} monitor metrics records older than {$cutoffDate->toDateTimeString()}");
            
            $totalDeleted = $deletedCount + $deletedChecks + $deletedMetrics;
            $this->info("✓ Total records deleted: {$totalDeleted}");
            $this->info("✓ Cleanup completed successfully!");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("✗ Error during cleanup: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
