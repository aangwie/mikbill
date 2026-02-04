<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\CronLog;

// Helper function to log cron
function logCron($command, $status, $output = null) {
    CronLog::create([
        'command' => $command,
        'status' => $status,
        'output' => $output
    ]);
}

// Jalankan perintah billing:check-unpaid setiap hari jam 00:01
Schedule::command('billing:check-unpaid')
    ->dailyAt('00:01')
    ->after(fn($output) => logCron('billing:check-unpaid', 'success', $output))
    ->onFailure(fn($output) => logCron('billing:check-unpaid', 'failed', $output));

Schedule::command('hotspot:cleanup')
    ->hourly()
    ->after(fn($output) => logCron('hotspot:cleanup', 'success', $output))
    ->onFailure(fn($output) => logCron('hotspot:cleanup', 'failed', $output));

Schedule::command('whatsapp:process-scheduled')
    ->everyMinute()
    ->after(fn($output) => logCron('whatsapp:process-scheduled', 'success', $output))
    ->onFailure(fn($output) => logCron('whatsapp:process-scheduled', 'failed', $output));