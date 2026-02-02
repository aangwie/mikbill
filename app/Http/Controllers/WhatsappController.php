<?php

namespace App\Http\Controllers;

use App\Models\WhatsappSetting;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\ScheduledMessage;
use App\Models\User;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WhatsappController extends Controller
{
    protected $waService;

    public function __construct(WhatsappService $waService)
    {
        $this->waService = $waService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $plan = $user->plan;
        $selectedAdminId = $request->input('admin_id');

        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            if (!$plan || !$plan->wa_gateway) {
                return redirect()->route('router.index')->with('warning', 'Layanan WhatsApp Gateway tidak tersedia di paket Anda. Silakan upgrade paket.');
            }
        }

        $setting = WhatsappSetting::first();

        // Global Adsense: Fetch any record that has adsense content (bypassing all scopes)
        $globalAdsense = WhatsappSetting::withoutGlobalScopes()
            ->whereNotNull('adsense_content')
            ->where('adsense_content', '!=', '')
            ->first();

        // Ambil pelanggan yang punya Nomor HP saja
        $customerQuery = Customer::whereNotNull('phone')
            ->where('phone', '!=', '');

        if ($user->role == 'superadmin' && $selectedAdminId) {
            $customerQuery->where('admin_id', $selectedAdminId);
        }

        $customers = $customerQuery->orderBy('name', 'asc')->get();

        $admins = [];
        if ($user->role == 'superadmin') {
            $admins = User::whereIn('role', ['admin', 'superadmin'])->get(['id', 'name', 'role']);
        }

        // Fetch scheduled messages (history & queue)
        $scheduledMessages = ScheduledMessage::orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('whatsapp.index', compact('setting', 'customers', 'globalAdsense', 'scheduledMessages', 'admins', 'selectedAdminId'));
    }

    // Simpan Konfigurasi
    public function update(Request $request)
    {
        $user = auth()->user();
        $rules = [
            'target_url' => 'required|url',
            'api_key' => 'required|string',
            'sender_number' => 'nullable|string',
        ];

        if ($user->isSuperAdmin()) {
            $rules['adsense_content'] = 'nullable|string';
            $rules['adsense_url'] = 'nullable|url';
        }

        $data = $request->validate($rules);

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

    // HALAMAN UTAMA BROADCAST
    public function broadcastIndex()
    {
        // Ambil ID, Nama, dan No HP semua pelanggan yang punya nomor HP
        $targets = Customer::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->select('id', 'name', 'phone')
            ->get();

        return view('whatsapp.broadcast', compact('targets'));
    }

    // PROSES KIRIM PER ITEM (Dipanggil AJAX)
    public function broadcastProcess(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'message' => 'required',
        ]);

        $customer = Customer::find($request->id);

        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan']);
        }

        // Replace variable dinamis
        $msg = str_replace('{name}', $customer->name, $request->message);
        $msg = str_replace('{tagihan}', number_format($customer->monthly_price, 0, ',', '.'), $msg);

        // Kirim WA
        try {
            $result = $this->waService->send($customer->phone, $msg);

            if ($result['status']) {
                return response()->json([
                    'status' => true,
                    'target' => $customer->name,
                    'phone' => $customer->phone
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'target' => $customer->name,
                    'message' => 'Gagal koneksi WA'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'target' => $customer->name,
                'message' => $e->getMessage()
            ]);
        }
    }

    // API: Ambil Daftar Target untuk Broadcast (Dipanggil AJAX)
    public function getBroadcastTargets(Request $request)
    {
        $type = $request->type; // 'unpaid', 'all', atau 'custom'
        $customerIds = $request->customer_ids; // Array of customer IDs for custom selection
        $whatsappAge = $request->whatsapp_age ?? '12+';
        $adminId = $request->admin_id;
        $user = auth()->user();

        // Get max recipients based on WhatsApp age
        $maxRecipients = ScheduledMessage::getMaxRecipients($whatsappAge);

        $query = Customer::whereNotNull('phone')
            ->where('phone', '!=', '');

        if ($user->role == 'superadmin' && $adminId) {
            $query->where('admin_id', $adminId);
        }

        if ($type == 'unpaid') {
            $query->whereHas('invoices', function ($q) {
                $q->where('status', '!=', 'paid');
            });
        } elseif ($type == 'custom' && !empty($customerIds)) {
            $query->whereIn('id', $customerIds);
        }

        $targets = $query->limit($maxRecipients)
            ->get(['id', 'name', 'phone', 'monthly_price']);

        return response()->json([
            'targets' => $targets,
            'max_recipients' => $maxRecipients,
            'total_available' => $targets->count()
        ]);
    }

    // API: Get customers for broadcast selection
    public function getCustomersForBroadcast(Request $request)
    {
        $customers = Customer::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'phone']);

        return response()->json($customers);
    }

    // Schedule or immediately process broadcast
    public function scheduleBroadcast(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'selection_mode' => 'required|in:all,custom',
            'whatsapp_age' => 'required|in:1-6,6-12,12+',
            'schedule_mode' => 'required|in:now,scheduled',
            'scheduled_at' => 'required_if:schedule_mode,scheduled|nullable|date',
            'customer_ids' => 'required_if:selection_mode,custom|nullable|array',
        ]);

        $user = auth()->user();
        $whatsappAge = $request->whatsapp_age;
        $maxRecipients = ScheduledMessage::getMaxRecipients($whatsappAge);

        // Get customer IDs based on selection mode
        if ($request->selection_mode === 'custom') {
            $customerIds = $request->customer_ids;
        } else {
            // All customers
            $customerIds = Customer::whereNotNull('phone')
                ->where('phone', '!=', '')
                ->limit($maxRecipients)
                ->pluck('id')
                ->toArray();
        }

        // Enforce limit based on WhatsApp age
        if (count($customerIds) > $maxRecipients) {
            $customerIds = array_slice($customerIds, 0, $maxRecipients);
        }

        // Determine scheduled time
        $scheduledAt = null;
        if ($request->schedule_mode === 'scheduled' && $request->scheduled_at) {
            $scheduledAt = Carbon::parse($request->scheduled_at);
        }

        // Create scheduled message record
        $scheduledMessage = ScheduledMessage::create([
            'admin_id' => $user->id,
            'message' => $request->message,
            'customer_ids' => $customerIds,
            'whatsapp_age' => $whatsappAge,
            'scheduled_at' => $scheduledAt,
            'status' => $scheduledAt ? 'pending' : 'processing',
            'total_count' => count($customerIds),
        ]);

        // If immediate send, return the customer list for AJAX processing
        if (!$scheduledAt) {
            $targets = Customer::whereIn('id', $customerIds)
                ->whereNotNull('phone')
                ->get(['id', 'name', 'phone', 'monthly_price']);

            return response()->json([
                'status' => true,
                'mode' => 'immediate',
                'scheduled_message_id' => $scheduledMessage->id,
                'targets' => $targets,
                'total' => count($customerIds),
                'message' => 'Broadcast dimulai...'
            ]);
        }

        // Scheduled for later
        return response()->json([
            'status' => true,
            'mode' => 'scheduled',
            'scheduled_message_id' => $scheduledMessage->id,
            'scheduled_at' => $scheduledAt->format('d M Y H:i'),
            'total' => count($customerIds),
            'message' => 'Broadcast dijadwalkan untuk ' . $scheduledAt->format('d M Y H:i')
        ]);
    }

    // Update scheduled message progress (called by AJAX during immediate broadcast)
    public function updateBroadcastProgress(Request $request)
    {
        $request->validate([
            'scheduled_message_id' => 'required|exists:scheduled_messages,id',
            'success_count' => 'required|integer',
            'failed_count' => 'required|integer',
            'status' => 'required|in:processing,completed,failed',
        ]);

        $scheduledMessage = ScheduledMessage::find($request->scheduled_message_id);
        $scheduledMessage->update([
            'success_count' => $request->success_count,
            'failed_count' => $request->failed_count,
            'status' => $request->status,
        ]);

        return response()->json(['status' => true]);
    }

    public function destroyScheduled($id)
    {
        $message = ScheduledMessage::findOrFail($id);

        // Security check: only allow owners or superadmins
        if ($message->admin_id !== auth()->id() && !auth()->user()->isSuperAdmin()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $message->delete();

        return response()->json(['status' => true, 'message' => 'Jadwal pesan berhasil dihapus.']);
    }

    public function regenerateApiKey()
    {
        $user = auth()->user();
        $user->api_token = \Illuminate\Support\Str::random(60);
        $user->save();

        return back()->with('success', 'API Key Generated successfully.');
    }
}
