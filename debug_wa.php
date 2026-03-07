<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use GuzzleHttp\Client;
use App\Models\WhatsappSetting;

// Simple script to test gateway connection
echo "Testing WhatsApp Gateway Connectivity...\n";

// Load first setting
$setting = WhatsappSetting::withoutGlobalScopes()->where('wa_provider', 'gateway')->first();

if (!$setting) {
    die("No gateway settings found in database.\n");
}

$url = rtrim($setting->wa_gateway_url, '/');
$session = $setting->gateway_session;
$apiKey = $setting->api_key_gateway;

echo "Target URL: $url\n";
echo "Session: $session\n";
echo "API Key Prefix: " . substr($apiKey, 0, 8) . "...\n";

$client = new Client();

try {
    echo "Requesting /status (Root)...\n";
    $response = $client->get($url . '/status', [
        'query' => ['session' => $session],
        'headers' => ['x-api-key' => $apiKey],
        'timeout' => 5,
        'verify' => false,
        'http_errors' => false
    ]);

    echo "HTTP Status (Root): " . $response->getStatusCode() . "\n";

    echo "Requesting /whatsapp-gateway/status (Subpath)...\n";
    $responseSub = $client->get($url . '/whatsapp-gateway/status', [
        'query' => ['session' => $session],
        'headers' => ['x-api-key' => $apiKey],
        'timeout' => 5,
        'verify' => false,
        'http_errors' => false
    ]);

    echo "HTTP Status (Subpath): " . $responseSub->getStatusCode() . "\n";
    echo "Response Body (Subpath): " . $responseSub->getBody()->getContents() . "\n";

    if ($response->getStatusCode() == 200) {
        echo "\nSUCCESS: Gateway is reachable and responding correctly.\n";
    } else {
        echo "\nERROR: Gateway returned status " . $response->getStatusCode() . ". check logs.\n";
    }

} catch (\Exception $e) {
    echo "\nEXCEPTION: " . $e->getMessage() . "\n";
    echo "This usually indicates a network issue or the gateway service is down.\n";
}
