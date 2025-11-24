<?php

namespace App\Http\Controllers;

use App\Models\WhatsappSetting;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\WhatsappService;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    protected $waService;

    public function __construct(WhatsappService $waService)
    {
        $this->waService = $waService;
    }

    public function index()
    {
        $setting = WhatsappSetting::first();

        // Ambil pelanggan yang punya Nomor HP saja
        $customers = Customer::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('name', 'asc')
            ->get();

        return view('whatsapp.index', compact('setting', 'customers'));
    }

    // Simpan Konfigurasi
    public function update(Request $request)
    {
        $data = $request->validate([
            'target_url' => 'required|url',
            'api_key' => 'required|string',
            'sender_number' => 'nullable|string',
        ]);

        $setting = WhatsappSetting::first();
        if ($setting) {
            $setting->update($data);
        } else {
            WhatsappSetting::create($data);
        }

        return back()->with('success', 'Pengaturan WhatsApp disimpan.');
    }

    // Test Kirim Pesan (Satu Nomor)
    public function sendTest(Request $request)
    {
        $request->validate(['target' => 'required', 'message' => 'required']);

        $result = $this->waService->send($request->target, $request->message);

        if ($result['status']) {
            return back()->with('success', 'Pesan terkirim! Response: ' . $result['response']);
        } else {
            return back()->with('error', 'Gagal: ' . $result['message']);
        }
    }

    // Broadcast (Masal)
    public function broadcast(Request $request)
    {
        $type = $request->type; // 'unpaid', 'paid', 'all'
        $messageTemplate = $request->message;

        $targets = [];

        if ($type == 'unpaid') {
            // Ambil user yang punya invoice unpaid
            $invoices = Invoice::with('customer')->where('status', 'unpaid')->get();
            foreach ($invoices as $inv) {
                $targets[] = [
                    'phone' => $inv->customer->phone,
                    'name' => $inv->customer->name,
                    'bill' => $inv->customer->monthly_price
                ];
            }
        } elseif ($type == 'all') {
            $customers = Customer::whereNotNull('phone')->get();
            foreach ($customers as $c) {
                $targets[] = ['phone' => $c->phone, 'name' => $c->name, 'bill' => 0];
            }
        }

        $count = 0;
        foreach ($targets as $target) {
            if (!empty($target['phone'])) {
                // Replace variabel dinamis {name} dan {bill}
                $msg = str_replace('{name}', $target['name'], $messageTemplate);
                $msg = str_replace('{tagihan}', number_format($target['bill']), $msg);

                $this->waService->send($target['phone'], $msg);
                $count++;
            }
        }

        return back()->with('success', "Broadcast sedang diproses ke $count nomor.");
    }

    // Kirim ke Satu Pelanggan (Dipilih dari Dropdown)
    // Kirim ke BANYAK Pelanggan (Multi Select)
    public function sendToCustomer(Request $request)
    {
        // Validasi: customer_ids sekarang harus berupa ARRAY
        $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:customers,id', // Pastikan setiap ID valid
            'message' => 'required',
        ]);

        $successCount = 0;
        $failCount = 0;

        // Loop setiap customer yang dipilih
        foreach ($request->customer_ids as $id) {
            $customer = Customer::find($id);

            if ($customer && !empty($customer->phone)) {
                // Replace variable {name} & {tagihan} unik per customer
                $msg = str_replace('{name}', $customer->name, $request->message);
                $msg = str_replace('{tagihan}', number_format($customer->monthly_price, 0, ',', '.'), $msg);

                // Kirim Pesan
                $result = $this->waService->send($customer->phone, $msg);

                if ($result['status']) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }
        }

        // Berikan feedback hasil pengiriman
        if ($successCount > 0) {
            return back()->with('success', "Pesan berhasil dikirim ke $successCount pelanggan." . ($failCount > 0 ? " ($failCount gagal)" : ""));
        } else {
            return back()->with('error', "Gagal mengirim pesan. Periksa nomor tujuan.");
        }
    }
}
