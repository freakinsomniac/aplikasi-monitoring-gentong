<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule monitor checks to run every minute, but monitors will check based on their individual interval_seconds
// For 10-second intervals, individual monitors will handle the timing
Schedule::command('monitor:check')->everyMinute();
