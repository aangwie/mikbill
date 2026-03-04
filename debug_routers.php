<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RouterSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

function check_for_user($userId)
{
    $user = User::find($userId);
    if (!$user) {
        echo "User $userId not found.\n";
        return;
    }
    Auth::login($user);
    echo "Checking for User: {$user->name} (Role: {$user->role}, ID: {$user->id})\n";

    // Test base query (scoped)
    $scopedCount = RouterSetting::count();
    $scopedSql = RouterSetting::toSql();
    echo "  Scoped Count: $scopedCount\n";
    echo "  Scoped SQL: $scopedSql\n";

    // Test unscoped query
    $unscopedCount = RouterSetting::withoutGlobalScopes()->count();
    echo "  Unscoped Count: $unscopedCount\n";

    // Check specific controller logic for Superadmin
    if ($user->isSuperAdmin()) {
        $query = RouterSetting::withoutGlobalScope(\App\Scopes\TenantScope::class)->orderBy('is_active', 'desc');
        echo "  Controller Superadmin Count: " . $query->count() . "\n";
        echo "  Controller Superadmin SQL: " . $query->toSql() . "\n";
    }
    echo "---------------------------------\n";
}

echo "Database Dump of router_settings:\n";
$all = RouterSetting::withoutGlobalScopes()->get();
foreach ($all as $r) {
    echo "ID: {$r->id}, Label: {$r->label}, Admin ID: " . ($r->admin_id ?? 'NULL') . ", Active: " . ($r->is_active ? 'YES' : 'NO') . "\n";
}
echo "=================================\n";

// Check for Superadmin (ID 4 based on previous check)
check_for_user(4);

// Check for an Admin (ID 1 based on previous check)
check_for_user(1);
