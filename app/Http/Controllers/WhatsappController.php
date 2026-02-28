<?php

namespace App\Http\Controllers;

use App\Models\WhatsappSetting;
use App\Models\WaBillTemplate;
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

        // Ambil setting milik admin sendiri (termasuk superadmin)
        if ($user->role == 'superadmin') {
            $setting = WhatsappSetting::withoutGlobalScopes()->where('admin_id', $user->id)->first();
        } else {
            $setting = WhatsappSetting::first();
        }

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

        // Fetch bill templates based on role
        if ($user->role == 'superadmin') {
            $billTemplates = WaBillTemplate::with('admin')->orderBy('name', 'asc')->get();
        } else {
            $billTemplates = WaBillTemplate::where('admin_id', $user->id)->orderBy('name', 'asc')->get();
        }

        return view('whatsapp.index', compact('setting', 'customers', 'globalAdsense', 'scheduledMessages', 'admins', 'selectedAdminId', 'billTemplates'));
    }

    // Simpan Konfigurasi
    public function update(Request $request)
    {
        $user = auth()->user();
        $rules = [
            'wa_provider' => 'required|in:api,gateway',
            'target_url' => 'nullable|url',
            'api_key_external' => 'nullable|string',
            'api_key_gateway' => 'nullable|string',
            'sender_number' => 'nullable|string',
            'wa_gateway_url' => 'nullable|url',
        ];

        if ($user->isSuperAdmin()) {
            $rules['adsense_content'] = 'nullable|string';
            $rules['adsense_url'] = 'nullable|url';
        }

        $data = $request->validate($rules);

        // Auto generate API Key Gateway if empty and provider is gateway
        if ($user->role == 'superadmin') {
            $setting = WhatsappSetting::withoutGlobalScopes()->where('admin_id', $user->id)->first();
        } else {
            $setting = WhatsappSetting::first();
        }

        // Auto generate API Key Gateway if empty and provider is gateway
        if ($data['wa_provider'] === 'gateway' && empty($data['api_key_gateway'])) {
            $data['api_key_gateway'] = \Illuminate\Support\Str::random(32);
        }

        // Always ensure a unique Gateway Session exists
        if (!$setting || empty($setting->gateway_session)) {
            $data['gateway_session'] = 'sess_' . \Illuminate\Support\Str::random(12);
        }

        if ($setting) {
            $setting->update($data);
        } else {
            // Set admin_id eksplisit jika superadmin (karena trait BelongsToTenant biasanya handle ini tapi kita pastikan)
            if ($user->role == 'superadmin') {
                $data['admin_id'] = $user->id;
            }
            WhatsappSetting::create($data);
        }

        return back()->with('success', 'Pengaturan WhatsApp disimpan.');
    }

    // Test Kirim Pesan (Satu Nomor)
    public function sendTest(Request $request)
    {
        $request->validate(['target' => 'required', 'message' => 'required']);

        $adminId = auth()->id();
        $result = $this->waService->send($request->target, $request->message, $adminId);

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

                $adminId = auth()->id();
                $this->waService->send($target['phone'], $msg, $adminId);
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
                $adminId = auth()->id();
                $result = $this->waService->send($customer->phone, $msg, $adminId);

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
            $adminId = auth()->id();
            $result = $this->waService->send($customer->phone, $msg, $adminId);

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
                    'message' => $result['message'] ?? 'Gagal koneksi/Error WA'
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

    // Schedule or immediately process unpaid broadcast
    public function scheduleUnpaidBroadcast(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'whatsapp_age' => 'required|in:1-6,6-12,12+',
            'schedule_mode' => 'required|in:now,scheduled',
            'scheduled_at' => 'required_if:schedule_mode,scheduled|nullable|date',
        ]);

        $user = auth()->user();
        $whatsappAge = $request->whatsapp_age;
        $maxRecipients = ScheduledMessage::getMaxRecipients($whatsappAge);
        $adminId = $request->admin_id;

        // Get unpaid customer IDs
        $query = Customer::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->whereHas('invoices', function ($q) {
                $q->where('status', '!=', 'paid');
            });

        if ($user->role == 'superadmin' && $adminId) {
            $query->where('admin_id', $adminId);
        }

        $customerIds = $query->limit($maxRecipients)->pluck('id')->toArray();

        if (empty($customerIds)) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada pelanggan dengan tagihan belum lunas.'
            ]);
        }

        // Enforce limit
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
            'broadcast_type' => 'unpaid',
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
                'message' => 'Broadcast tagihan dimulai...'
            ]);
        }

        // Scheduled for later
        return response()->json([
            'status' => true,
            'mode' => 'scheduled',
            'scheduled_message_id' => $scheduledMessage->id,
            'scheduled_at' => $scheduledAt->format('d M Y H:i'),
            'total' => count($customerIds),
            'message' => 'Broadcast tagihan dijadwalkan untuk ' . $scheduledAt->format('d M Y H:i')
        ]);
    }

    // Store a new bill template
    public function storeBillTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $template = WaBillTemplate::create([
            'admin_id' => auth()->id(),
            'name' => $request->name,
            'content' => $request->input('content'),
        ]);

        $template->load('admin');

        return response()->json([
            'status' => true,
            'message' => 'Template berhasil disimpan.',
            'template' => $template,
        ]);
    }

    // Delete a bill template
    public function destroyBillTemplate($id)
    {
        $template = WaBillTemplate::findOrFail($id);
        $user = auth()->user();

        // Admin can only delete own templates; superadmin can delete any
        if ($user->role !== 'superadmin' && $template->admin_id !== $user->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $template->delete();

        return response()->json([
            'status' => true,
            'message' => 'Template berhasil dihapus.',
        ]);
    }

    public function regenerateApiKey()
    {
        $user = auth()->user();
        $user->api_token = \Illuminate\Support\Str::random(60);
        $user->save();

        return back()->with('success', 'User API Token generated successfully.');
    }

    public function regenerateGatewayApiKey()
    {
        $user = auth()->user();

        if ($user->role == 'superadmin') {
            $setting = WhatsappSetting::withoutGlobalScopes()->where('admin_id', $user->id)->first();
        } else {
            $setting = WhatsappSetting::first();
        }

        if (!$setting) {
            return back()->with('error', 'Silakan simpan konfigurasi WhatsApp terlebih dahulu.');
        }

        $setting->update([
            'api_key_gateway' => \Illuminate\Support\Str::random(32)
        ]);

        return back()->with('success', 'WhatsApp Gateway API Key regenerated successfully.');
    }

    // --- GATEWAY HELPERS (AJAX) ---

    public function getGatewayStatus()
    {
        $user = auth()->user();
        if ($user->role == 'superadmin') {
            $setting = WhatsappSetting::withoutGlobalScopes()->where('admin_id', $user->id)->first();
        } else {
            $setting = WhatsappSetting::first();
        }

        $gatewayUrl = $setting->wa_gateway_url ?? 'http://localhost:3000';
        $sessionId = $setting->gateway_session;

        if (!$sessionId) {
            return response()->json(['status' => 'disconnected', 'message' => 'Session not initialized']);
        }

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($gatewayUrl . '/status', [
                'query' => ['session' => $sessionId],
                'timeout' => 5
            ]);
            return response()->json(json_decode($response->getBody()->getContents(), true));
        } catch (\Exception $e) {
            return response()->json(['status' => 'disconnected', 'message' => 'Gateway offline']);
        }
    }

    public function logoutGateway()
    {
        $user = auth()->user();
        if ($user->role == 'superadmin') {
            $setting = WhatsappSetting::withoutGlobalScopes()->where('admin_id', $user->id)->first();
        } else {
            $setting = WhatsappSetting::first();
        }

        $gatewayUrl = $setting->wa_gateway_url ?? 'http://localhost:3000';
        $sessionId = $setting->gateway_session;

        if (!$sessionId) {
            return response()->json(['status' => false, 'message' => 'Session not initialized']);
        }

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post($gatewayUrl . '/logout', [
                'json' => ['session' => $sessionId],
                'timeout' => 5
            ]);
            return response()->json(json_decode($response->getBody()->getContents(), true));
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Gateway offline']);
        }
    }
}
