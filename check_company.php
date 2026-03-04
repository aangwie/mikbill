<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate what AppServiceProvider does
echo "=== AppServiceProvider Logic Simulation ===\n\n";

// Step 1: Try superadmin company
$company = App\Models\Company::withoutGlobalScopes()->whereHas('admin', function ($q) {
    $q->where('role', 'superadmin');
})->first();

echo "1. Superadmin company: " . ($company ? "FOUND (id={$company->id})" : "NOT FOUND") . "\n";

// Step 2: Fallback to first
if (!$company) {
    $company = App\Models\Company::withoutGlobalScopes()->first();
    echo "2. Fallback company: " . ($company ? "FOUND (id={$company->id})" : "NOT FOUND") . "\n";
}

if ($company) {
    echo "\nCompany Details:\n";
    echo "  Name: {$company->company_name}\n";
    echo "  Logo Path: [{$company->logo_path}]\n";
    echo "  Admin ID: {$company->admin_id}\n";

    if ($company->logo_path) {
        $fullPath = __DIR__ . '/public/uploads/' . $company->logo_path;
        echo "  Full logo path: {$fullPath}\n";
        echo "  File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
        echo "  Asset URL would be: " . url('uploads/' . $company->logo_path) . "\n";
    } else {
        echo "  *** LOGO PATH IS EMPTY ***\n";
    }
}

// Check what admin user 7 is
$user7 = App\Models\User::find(7);
echo "\nUser #7: " . ($user7 ? "{$user7->name} (role: {$user7->role})" : "NOT FOUND") . "\n";

// Check what superadmin user is 
$superadmin = App\Models\User::where('role', 'superadmin')->first();
echo "Superadmin User: " . ($superadmin ? "#{$superadmin->id} {$superadmin->name}" : "NOT FOUND") . "\n";

// Now simulate with TenantScope as AppServiceProvider does (NO withoutGlobalScopes)
echo "\n=== With TenantScope (no auth user - like boot()) ===\n";
$company2 = App\Models\Company::whereHas('admin', function ($q) {
    $q->where('role', 'superadmin');
})->first();
echo "Superadmin company (with scope): " . ($company2 ? "FOUND (id={$company2->id})" : "NOT FOUND") . "\n";

$company3 = App\Models\Company::first();
echo "First company (with scope): " . ($company3 ? "FOUND (id={$company3->id}, logo={$company3->logo_path})" : "NOT FOUND") . "\n";
