<?php

namespace App\Http\Controllers;

use App\Models\PaymentSetting;
use Illuminate\Http\Request;

class PaymentSettingController extends Controller
{
    public function index()
    {
        $setting = PaymentSetting::firstOrCreate([], ['provider' => 'xendit']);
        return view('superadmin.settings.payment', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'active_provider' => 'required|in:xendit,midtrans',
            // Xendit validation (conditional in real life, but simple for now)
            'xendit_api_key' => 'nullable|string',
            'xendit_callback_token' => 'nullable|string',
            // Midtrans validation
            'midtrans_server_key' => 'nullable|string',
            'midtrans_client_key' => 'nullable|string',
        ]);

        $setting = PaymentSetting::first();
        $setting->update([
            'active_provider' => $request->active_provider,
            'xendit_api_key' => $request->xendit_api_key,
            'xendit_callback_token' => $request->xendit_callback_token,
            'midtrans_server_key' => $request->midtrans_server_key,
            'midtrans_client_key' => $request->midtrans_client_key,
            'midtrans_is_production' => $request->has('midtrans_is_production'),
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Pengaturan pembayaran berhasil diperbarui.');
    }
}
