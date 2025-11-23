<?php

namespace App\Services;

use App\Models\WhatsappSetting;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public function send($targetNumber, $message)
    {
        // 1. Ambil Konfigurasi dari DB
        $config = WhatsappSetting::first();
        
        if (!$config) {
            return ['status' => false, 'message' => 'Konfigurasi WA belum diatur.'];
        }

        // 2. Format Nomor (Pastikan 628...)
        // Menghapus 0 di depan jika ada, ganti dengan 62 (opsional, sesuaikan provider)
        if (substr($targetNumber, 0, 1) == '0') {
            $targetNumber = '62' . substr($targetNumber, 1);
        }

        // 3. Siapkan Data Payload (JSON)
        // PENTING: Sesuaikan struktur array ini dengan Dokumentasi API Provider Anda
        $data = [
            'api_key' => $config->api_key,
            'sender'  => $config->sender_number,
            'number'  => $targetNumber,
            'message' => $message,
        ];

        // 4. Eksekusi cURL
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $config->target_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            Log::error("WA Error: " . $err);
            return ['status' => false, 'message' => 'cURL Error: ' . $err];
        }

        return ['status' => true, 'response' => $response];
    }
}