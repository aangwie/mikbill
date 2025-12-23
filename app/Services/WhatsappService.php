<?php

namespace App\Services;

use App\Models\WhatsappSetting;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public function send($targetNumber, $message)
    {
        // 1. Format Nomor (Pastikan 628...)
        if (substr($targetNumber, 0, 1) == '0') {
            $targetNumber = '62' . substr($targetNumber, 1);
        }

        // 2. Kirim ke Node.js Gateway Local
        $url = 'http://localhost:3000/send';

        $data = [
            'number' => $targetNumber,
            'message' => $message,
        ];

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post($url, [
                'json' => $data,
                'http_errors' => false
            ]);

            $body = json_decode($response->getBody(), true);

            if ($response->getStatusCode() == 200 && ($body['status'] ?? false)) {
                return ['status' => true, 'response' => 'Sent via Gateway'];
            } else {
                return ['status' => false, 'message' => $body['message'] ?? 'Unknown Error'];
            }
        } catch (\Exception $e) {
            Log::error("WA Gateway Error: " . $e->getMessage());
            return ['status' => false, 'message' => 'Gateway Error: ' . $e->getMessage()];
        }
    }
}