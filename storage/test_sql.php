<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::enableQueryLog();
$user = \App\Models\User::find(2); // ID 2 is an admin? Wait, 1 and 7 are admins.
$user = \App\Models\User::find(1);

\Illuminate\Support\Facades\Auth::login($user);

$customerQuery = \App\Models\Customer::query();
if ($user->role === 'admin') {
    $customerQuery->where('admin_id', $user->id);
}
$count = $customerQuery->count();

$log = DB::getQueryLog();
echo "SQL: " . end($log)['query'] . "\n";
echo "Bindings: " . json_encode(end($log)['bindings']) . "\n";
echo "Count: $count\n";
