<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Re-fetch all Sage 300 items every day at midnight so the catalogue stays fresh.
// Requires the Windows Task Scheduler entry below — only needs to be set up once on the XAMPP server.
Schedule::command('sage300:warm-items --force')->dailyAt('00:00');
