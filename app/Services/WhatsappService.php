<?php

namespace App\Services;

use App\Models\WhatsappSetting;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public static function send($targetNumber, $message)
    {
        // 1. Ambil Pengaturan dari Database
        $setting = WhatsappSetting::first();
        if (!$setting) {
            return ['status' => false, 'message' => 'Pengaturan WhatsApp belum dikonfigurasi.'];
        }

        // 2. Format Nomor (Pastikan 628...)
        if (substr($targetNumber, 0, 1) == '0') {
            $targetNumber = '62' . substr($targetNumber, 1);
        }

        // 3. Persiapkan Data (Sesuai parameter yang diminta user: host, api_key, sender)
        $url = $setting->target_url;
        $apiKey = $setting->api_key;
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
                'form_params' => $data, // Gunakan form_params atau json tergantung provider, biasanya form
                'timeout' => 10,
                'http_errors' => false
            ]);

            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);

            // Log response untuk debugging
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