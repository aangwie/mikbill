<?php

namespace App\Services;

use App\Models\WhatsappSetting;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public static function send($targetNumber, $message, $adminId = null)
    {
        // 1. Ambil Pengaturan dari Database
        if ($adminId) {
            // Jika adminId diberikan (misal dari cron), ambil secara eksplisit
            $setting = WhatsappSetting::withoutGlobalScopes()
                ->where('admin_id', $adminId)
                ->first();
        } else {
            // Jika tidak ada adminId, gunakan scope default (yg login)
            $setting = WhatsappSetting::first();
        }

        if (!$setting) {
            return ['status' => false, 'message' => 'Pengaturan WhatsApp belum dikonfigurasi.'];
        }

        // 2. Format Nomor (Pastikan 628...)
        // Hapus karakter non-digit (seperti +, -, space)
        $targetNumber = preg_replace('/[^0-9]/', '', $targetNumber);

        if (substr($targetNumber, 0, 1) == '0') {
            $targetNumber = '62' . substr($targetNumber, 1);
        }

        // 3. Cek Provider: API atau Gateway
        if ($setting->wa_provider === 'gateway') {
            // KIRIM VIA SELF-HOSTED GATEWAY (BAILEYS)
            $gatewayUrl = $setting->wa_gateway_url;
            if (empty($gatewayUrl)) {
                $saSetting = WhatsappSetting::withoutGlobalScopes()
                    ->whereHas('admin', function ($q) {
                        $q->where('role', 'superadmin'); })
                    ->first();
                $gatewayUrl = $saSetting->wa_gateway_url ?? 'http://localhost:3000';
            }
            $gatewayUrl = $gatewayUrl ?? 'http://localhost:3000';

            $url = rtrim($gatewayUrl, '/') . '/send';
            $data = [
                'number' => $targetNumber,
                'message' => $message,
                'session' => $setting->gateway_session,
            ];

            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->post($url, [
                    'json' => $data,
                    'headers' => [
                        'x-api-key' => $setting->api_key_gateway,
                    ],
                    'timeout' => 15,
                    'verify' => false,
                    'http_errors' => false
                ]);

                $body = $response->getBody()->getContents();
                $result = json_decode($body, true);

                if ($response->getStatusCode() == 200 && isset($result['status']) && $result['status']) {
                    return ['status' => true, 'response' => $body];
                } else {
                    return ['status' => false, 'message' => 'Gateway Error: ' . ($result['message'] ?? 'Unknown error')];
                }
            } catch (\Exception $e) {
                Log::error("WA Gateway Exception: " . $e->getMessage());
                return ['status' => false, 'message' => 'Gateway Exception: ' . $e->getMessage()];
            }
        }

        // --- KIRIM VIA API EXTERNAL (Provider Lama) ---
        $url = $setting->target_url;
        $apiKey = $setting->api_key_external;
        $sender = $setting->sender_number;

        $data = [
            'api_key' => $apiKey,
            'nomor_pengirim' => $sender,
            'nomor_penerima' => $targetNumber,
            'pesan' => $message,
        ];

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post($url, [
                'form_params' => $data,
                'timeout' => 10,
                'http_errors' => false
            ]);

            $body = $response->getBody()->getContents();
            Log::info("WA API Response: " . $body);

            if ($response->getStatusCode() == 200) {
                return ['status' => true, 'response' => $body];
            } else {
                return ['status' => false, 'message' => 'API Error (' . $response->getStatusCode() . '): ' . $body];
            }
        } catch (\Exception $e) {
            Log::error("WA API Exception: " . $e->getMessage());
            return ['status' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }
}