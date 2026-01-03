<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PaymentSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class SubscriptionController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'cycle' => 'required|in:monthly,semester,annual'
        ]);

        $user = auth()->user();
        $plan = Plan::find($request->plan_id);

        if (!is_null($plan->stock_limit) && $plan->users()->count() >= $plan->stock_limit) {
            return back()->with('error', 'Maaf, stok paket ini sudah habis.');
        }

        $setting = PaymentSetting::where('is_active', true)->first();

        if (!$setting) {
            return back()->with('error', 'Metode pembayaran belum dikonfigurasi atau dinonaktifkan oleh Superadmin.');
        }

        $amount = 0;
        $description = "Berlangganan Paket {$plan->name}";

        switch ($request->cycle) {
            case 'monthly':
                $amount = $plan->price_monthly;
                break;
            case 'semester':
                $amount = $plan->price_semester;
                break;
            case 'annual':
                $amount = $plan->price_annual;
                break;
        }

        if ($amount <= 0) {
            return back()->with('error', 'Harga paket tidak valid.');
        }

        $external_id = 'sub_' . $user->id . '_' . time();

        if ($setting->active_provider === 'xendit') {
            return $this->checkoutXendit($setting, $user, $plan, $amount, $description, $external_id, $request->cycle);
        } else {
            return $this->checkoutMidtrans($setting, $user, $plan, $amount, $description, $external_id, $request->cycle);
        }
    }

    private function checkoutXendit($setting, $user, $plan, $amount, $description, $external_id, $cycle)
    {
        Configuration::setApiKey($setting->xendit_api_key);
        $apiInstance = new InvoiceApi();

        $create_invoice_request = new CreateInvoiceRequest([
            'external_id' => $external_id,
            'description' => $description . " - " . ucfirst($cycle),
            'amount' => $amount,
            'payer_email' => $user->email,
            'customer' => [
                'given_names' => $user->name,
                'email' => $user->email
            ],
            'success_redirect_url' => route('payment.finish'),
            'failure_redirect_url' => route('payment.error'),
            'currency' => 'IDR',
            'metadata' => [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'cycle' => $cycle
            ]
        ]);

        try {
            $result = $apiInstance->createInvoice($create_invoice_request);
            return redirect($result['invoice_url']);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat tagihan Xendit: ' . $e->getMessage());
        }
    }

    private function checkoutMidtrans($setting, $user, $plan, $amount, $description, $external_id, $cycle)
    {
        Config::$serverKey = $setting->midtrans_server_key;
        Config::$isProduction = (bool) $setting->midtrans_is_production;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $external_id,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [
                [
                    'id' => $plan->id,
                    'price' => $amount,
                    'quantity' => 1,
                    'name' => "Paket {$plan->name} (" . ucfirst($cycle) . ")",
                ]
            ],
            'metadata' => [ // Midtrans metadata is slightly different, but we can pass it
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'cycle' => $cycle
            ],
            // Midtrans usually takes metadata in 'custom_field1' for simple use
            'custom_field1' => json_encode([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'cycle' => $cycle
            ]),
            'callbacks' => [
                'finish' => route('payment.finish'),
                'unfinish' => route('payment.unfinish'),
                'error' => route('payment.error'),
            ]
        ];

        try {
            $snapUrl = Snap::createTransaction($params)->redirect_url;
            return redirect($snapUrl);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat tagihan Midtrans: ' . $e->getMessage());
        }
    }

    public function webhook(Request $request)
    {
        if ($request->isMethod('get')) {
            return response()->json(['message' => 'BillNesia Webhook Endpoint is Active. Use POST for notifications.'], 200);
        }

        $setting = PaymentSetting::first();
        if (!$setting)
            return response()->json(['message' => 'No settings'], 404);

        $data = $request->all();

        // Xendit Webhook Detection
        if ($request->header('x-callback-token')) {
            if ($request->header('x-callback-token') !== $setting->xendit_callback_token) {
                return response()->json(['message' => 'Invalid Xendit token'], 403);
            }
            if ($data['status'] === 'PAID') {
                $meta = $data['metadata'] ?? [];
                $this->activatePlan($meta['user_id'] ?? null, $meta['plan_id'] ?? null, $meta['cycle'] ?? null);
            }
            return response()->json(['status' => 'Xendit processed']);
        }

        // Midtrans Webhook Detection
        if (isset($data['transaction_status']) && isset($data['order_id'])) {
            // Verify signature for security
            Config::$serverKey = $setting->midtrans_server_key;
            Config::$isProduction = (bool) $setting->midtrans_is_production;

            try {
                $notif = new Notification();
                $status = $notif->transaction_status;

                if ($status == 'settlement' || $status == 'capture') {
                    $meta = json_decode($data['custom_field1'] ?? '{}', true);
                    $this->activatePlan($meta['user_id'] ?? null, $meta['plan_id'] ?? null, $meta['cycle'] ?? null);
                }
            } catch (\Exception $e) {
                return response()->json(['message' => 'Notification Error: ' . $e->getMessage()], 500);
            }

            return response()->json(['status' => 'Midtrans processed']);
        }

        return response()->json(['status' => 'No provider detected'], 400);
    }

    public function paymentFinish(Request $request)
    {
        return redirect()->route('router.index')->with('success', 'Pembayaran berhasil! Paket Anda telah diaktifkan otomatis.');
    }

    public function paymentUnfinish(Request $request)
    {
        return redirect()->route('plans.public')->with('warning', 'Pembayaran belum diselesaikan. Silakan cek kembali tagihan Anda.');
    }

    public function paymentError(Request $request)
    {
        return redirect()->route('plans.public')->with('error', 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.');
    }

    private function activatePlan($userId, $planId, $cycle)
    {
        if ($userId && $planId) {
            $user = User::find($userId);
            if ($user) {
                $days = 30;
                if ($cycle === 'semester')
                    $days = 180;
                if ($cycle === 'annual')
                    $days = 365;

                $user->plan_id = $planId;
                $user->plan_expires_at = now()->addDays($days);
                $user->is_activated = true;
                $user->save();
            }
        }
    }
}
