<?php

use Illuminate\Support\Facades\Schedule;

// Jalankan perintah billing:check-unpaid setiap hari jam 00:01
Schedule::command('billing:check-unpaid')->dailyAt('00:01');
Schedule::command('hotspot:cleanup')->hourly();
Schedule::command('whatsapp:process-scheduled')->everyMinute();