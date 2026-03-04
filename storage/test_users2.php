<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (\App\Models\User::all() as $u) {
    echo $u->id . ' - ' . $u->name . ' - ' . $u->role . PHP_EOL;
}
echo 'Total Customers: ' . \App\Models\Customer::withoutGlobalScopes()->count() . PHP_EOL;
echo 'Admin 1: ' . \App\Models\Customer::withoutGlobalScopes()->where('admin_id', 1)->count() . PHP_EOL;
echo 'Admin 2: ' . \App\Models\Customer::withoutGlobalScopes()->where('admin_id', 2)->count() . PHP_EOL;
echo 'Admin 4: ' . \App\Models\Customer::withoutGlobalScopes()->where('admin_id', 4)->count() . PHP_EOL;
echo 'Admin 7: ' . \App\Models\Customer::withoutGlobalScopes()->where('admin_id', 7)->count() . PHP_EOL;
