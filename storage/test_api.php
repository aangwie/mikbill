<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Get an admin user
$user = \App\Models\User::find(7);
// 2. Create a token
$token = $user->createToken('test-mobile-7')->plainTextToken;

// 3. Make HTTP request to local server
$client = new \GuzzleHttp\Client();
$res = $client->get('http://127.0.0.1:8000/api/mobile/dashboard', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ]
]);

echo "Response body:\n" . $res->getBody()->getContents() . "\n";
