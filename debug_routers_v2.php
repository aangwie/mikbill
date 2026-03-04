<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RouterSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

function check_role($userId)
{
    $user = User::find($userId);
    if (!$user) {
        echo "User ID $userId not found.\n";
        return;
    }
    Auth::login($user);
    echo "=================================\n";
    echo "User: {$user->name} (Role: {$user->role}, ID: {$user->id})\n";

    // Scoped query (normal)
    $scopedCount = RouterSetting::count();
    echo "Scoped Count: $scopedCount\n";

    // Unscoped query
    $unscopedCount = RouterSetting::withoutGlobalScopes()->count();
    echo "Unscoped Count: $unscopedCount\n";

    // Specific Controller logic for Superadmin
    if ($user->isSuperAdmin()) {
        $query = RouterSetting::withoutGlobalScope(\App\Scopes\TenantScope::class);
        echo "Superadmin Bypass Scope Count: " . $query->count() . "\n";
    }
}

echo "Database Dump:\n";
$all = RouterSetting::withoutGlobalScopes()->get();
foreach ($all as $r) {
    echo "ID: {$r->id}, Label: {$r->label}, Admin ID: " . ($r->admin_id ?? 'NULL') . "\n";
}

check_role(4); // Super Admin
check_role(1); // Admin
echo "=================================\n";
