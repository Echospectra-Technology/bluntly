<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule content moderation to run every minute
Schedule::command('moderate:content --batch-size=25')
    ->everyMinute()
    ->withoutOverlapping(5) // Prevent overlapping runs, timeout after 5 minutes
    ->onOneServer() // Only run on one server in multi-server setup
    ->runInBackground();

// Schedule AI persona action scheduling (every 15 minutes)
Schedule::command('ai:schedule-actions')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();

// Process pending AI actions (every minute)
Schedule::command('ai:process-actions')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();
