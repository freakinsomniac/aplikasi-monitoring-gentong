<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule monitor checks to run every minute, but monitors will check based on their individual interval_seconds
// For 10-second intervals, individual monitors will handle the timing
Schedule::command('monitor:check')->everySecond();

// Schedule cleanup of old monitoring logs (runs every 30 days at 2:00 AM)
// Deletes logs older than 30 days to prevent database bloat
Schedule::command('logs:cleanup')->monthlyOn(1, '02:00');
    