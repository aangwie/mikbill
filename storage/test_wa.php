<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::find(1);
// Mock Sanctum Auth
\Illuminate\Support\Facades\Auth::login($user);

// 1. What does WhatsappSetting::first() return?
$setting = \App\Models\WhatsappSetting::first();
echo "Setting for Admin 1: " . ($setting ? $setting->id : 'NULL') . "\n";

$result = \App\Services\WhatsappService::send('081234567890', 'Test message');
echo "Send result:\n";
print_r($result);
