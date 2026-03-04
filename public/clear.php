<?php

/**
 * Standalone Laravel Cache Clear Script
 * Upload this to your public/ directory on shared hosting and visit it in your browser.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');

echo "--- Laravel Cache Clear Tool ---\n\n";

try {
    echo "1. Clearing Config Cache...\n";
    Artisan::call('config:clear');
    echo Artisan::output() . "\n";

    echo "2. Clearing Route Cache...\n";
    Artisan::call('route:clear');
    echo Artisan::output() . "\n";

    echo "3. Clearing View Cache...\n";
    Artisan::call('view:clear');
    echo Artisan::output() . "\n";

    echo "4. Clearing Application Cache...\n";
    Artisan::call('cache:clear');
    echo Artisan::output() . "\n";

    echo "\nSUCCESS: All caches cleared successfully!\n";
    echo "Please delete this file (clear.php) after use for security reasons.\n";
} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
