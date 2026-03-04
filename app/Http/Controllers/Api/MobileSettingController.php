<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;

class MobileSettingController extends Controller
{
    /**
     * GET /api/mobile/app-config (Public)
     * Return app configuration for mobile client.
     */
    public function appConfig()
    {
        $setting = SiteSetting::first();

        return response()->json([
            'success' => true,
            'data' => [
                'mobile_api_url' => $setting->mobile_api_url ?? null,
                'app_name' => 'Billnesia Mobile',
                'app_version' => '1.0.0',
            ],
        ]);
    }
}
