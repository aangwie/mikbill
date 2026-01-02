<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Company;
use App\Services\MikrotikService;
use App\Services\WhatsappService; // 1. Import Service WA
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BillingController extends Controller
{
    protected $mikrotik;
    protected $wa; // 2. Property baru untuk WA

    // 3. Inject WhatsappService di Constructor
    public function __construct(MikrotikService $mikrotikService, WhatsappService $whatsappService)
    {
        $this->mikrotik = $mikrotikService;
        $this->wa = $whatsappService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Ambil Filter Bulan & Tahun (Default: Bulan Ini)
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        // Query Tagihan dengan Filter
        $invoiceQuery = Invoice::with('customer')
            ->whereMonth('due_date', $month)
            ->whereYear('due_date', $year)
            ->orderByRaw("FIELD(status, 'unpaid', 'paid')")
            ->orderBy('due_date', 'asc');

        if ($user->role == 'operator') {
            $invoiceQuery->whereHas('customer', function ($q) use ($user) {
                $q->where('operator_id', $user->id);
            });
        }

        $invoices = $invoiceQuery->get();

        // 2. Hitung Totals dari Data Terfilter
        $total_bill = 0;
        $paid_bill = 0;
        $unpaid_bill = 0;

        foreach ($invoices as $inv) {
            // Asumsi 'price' ada di tabel Invoice. 
            // Jika price di invoice null/0 (pake harga customer), logicnya harus disesuaikan.
            // Tapi biasanya saat generate, price disimpan. Kita pakai $inv->price langsung.
            // Jika $inv->price belum ada (masih ikut customer), ambil dari relation.
            // Untuk simplifikasi dan performa, sebaiknya saat create invoice price disimpan.
            // Cek implementation generate: Invoice::create([...]) -> price tdk di set? 
            // Jika tdk di set, berarti nol. Kita cek view: {{ number_format($inv->price, ...) }}
            // View pakai $inv->price. Jadi asumsi column price ada.

            // Perbaikan logic harga: Jika invoice price 0, ambil dari customer
            $price = $inv->price > 0 ? $inv->price : ($inv->customer->monthly_price ?? 0);

            $total_bill += $price;
            if ($inv->status == 'paid') {
                $paid_bill += $price;
            } else {
                $unpaid_bill += $price;
            }
        }

        $customerQuery = Customer::orderBy('name', 'asc');
        if ($user->role == 'operator') {
            $customerQuery->where('operator_id', $user->id);
        }
        $customers = $customerQuery->get();

        return view('billing.index', compact('invoices', 'customers', 'month', 'year', 'total_bill', 'paid_bill', 'unpaid_bill'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'month' => 'required|numeric',
            'year' => 'required|numeric',
            'due_date' => 'required|date',
        ]);

        $user = Auth::user();

        // Use global scope but add explicit filters for redundancy and clarity
        $query = Customer::where('is_active', true);

        if ($user->role == 'operator') {
            $query->where('operator_id', $user->id);
        } elseif ($user->role == 'admin') {
            $query->where('admin_id', $user->id);
        }

        $activeCustomers = $query->get();

        if ($activeCustomers->isEmpty()) {
            return back()->with('error', 'Tidak ada pelanggan aktif yang ditemukan untuk akun Anda.');
        }

        $count = 0;
        foreach ($activeCustomers as $customer) {
            $exists = Invoice::where('customer_id', $customer->id)
                ->where('status', 'unpaid')
                ->whereMonth('due_date', $request->month)
                ->whereYear('due_date', $request->year)
                ->exists();

            if (!$exists) {
                Invoice::create([
                    'customer_id' => $customer->id,
                    'admin_id' => $customer->admin_id, // Ensure admin_id is carried over
                    'due_date' => $request->due_date,
                    'price' => $customer->monthly_price, // Save current price
                    'status' => 'unpaid',
                ]);
                $count++;
            }
        }

        return back()->with('success', "Berhasil membuat $count tagihan baru.");
    }

    /**
     * AJAX: Get List of Customers for Bulk Generation
     */
    public function getList(Request $request)
    {
        $user = Auth::user();
        $query = Customer::where('is_active', true);

        if ($user->role == 'operator') {
            $query->where('operator_id', $user->id);
        } elseif ($user->role == 'admin') {
            $query->where('admin_id', $user->id);
        }

        $customers = $query->get(['id', 'name', 'monthly_price', 'admin_id']);

        return response()->json([
            'customers' => $customers,
            'total' => $customers->count()
        ]);
    }

    /**
     * AJAX: Process Single Billing Item
     */
    public function processItem(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'month' => 'required|numeric',
            'year' => 'required|numeric',
            'due_date' => 'required|date',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        // Double check ownership
        if (Auth::user()->role == 'operator' && $customer->operator_id != Auth::user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        if (Auth::user()->role == 'admin' && $customer->admin_id != Auth::user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $exists = Invoice::where('customer_id', $customer->id)
            ->where('status', 'unpaid')
            ->whereMonth('due_date', $request->month)
            ->whereYear('due_date', $request->year)
            ->exists();

        if ($exists) {
            return response()->json(['status' => 'skipped', 'name' => $customer->name]);
        }

        Invoice::create([
            'customer_id' => $customer->id,
            'admin_id' => $customer->admin_id,
            'due_date' => $request->due_date,
            'price' => $customer->monthly_price,
            'status' => 'unpaid',
        ]);

        return response()->json(['status' => 'created', 'name' => $customer->name]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'due_date' => 'required|date',
            'price' => 'nullable|numeric',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        if (Auth::user()->role == 'operator') {
            if ($customer->operator_id != Auth::user()->id) {
                return back()->with('error', 'Anda tidak berhak membuat tagihan untuk pelanggan ini.');
            }
        } elseif (Auth::user()->role == 'admin') {
            if ($customer->admin_id != Auth::user()->id) {
                return back()->with('error', 'Anda tidak berhak membuat tagihan untuk pelanggan ini.');
            }
        }

        Invoice::create([
            'customer_id' => $request->customer_id,
            'admin_id' => $customer->admin_id,
            'due_date' => $request->due_date,
            'price' => $request->price ?? $customer->monthly_price,
            'status' => 'unpaid',
        ]);

        return back()->with('success', 'Tagihan manual berhasil dibuat!');
    }

    /**
     * PROSES PEMBAYARAN (BAYAR & AKTIFKAN + KIRIM WA)
     */
    public function processPayment($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);
        $customer = $invoice->customer;
        $userPppoe = $customer->pppoe_username;

        // Validasi Operator
        if (Auth::user()->role == 'operator') {
            if ($customer->operator_id != Auth::user()->id) {
                return back()->with('error', 'Akses Ditolak.');
            }
        }

        // Update Database
        $invoice->update(['status' => 'paid']);
        $customer->update(['is_active' => true]);

        // Eksekusi Mikrotik
        $pesanMikrotik = "";
        try {
            if ($this->mikrotik->isConnected()) {
                $this->mikrotik->setSecretStatus($userPppoe, 'enabled');
                $this->mikrotik->kickUser($userPppoe);
                $pesanMikrotik = "Mikrotik: Enabled.";
            } else {
                $pesanMikrotik = "Mikrotik: Gagal Konek.";
            }
        } catch (\Exception $e) {
            $pesanMikrotik = "Mikrotik Error: " . $e->getMessage();
        }

        // --- KIRIM NOTIFIKASI WA (LUNAS) ---
        $pesanWA = "";
        if (!empty($customer->phone)) {
            $tglBayar = Carbon::now()->locale('id')->isoFormat('D MMMM Y, HH:mm');
            $nominal = number_format($customer->monthly_price, 0, ',', '.');
            $periode = Carbon::parse($invoice->due_date)->locale('id')->isoFormat('MMMM Y');

            // 1. GENERATE LINK DOWNLOAD INVOICE
            // Kita gunakan route frontend yang sudah ada
            $linkDownload = route('frontend.invoice', $invoice->id);

            $text = "*PEMBAYARAN DITERIMA*\n\n";
            $text .= "Halo {$customer->name},\n";
            $text .= "Terima kasih, pembayaran tagihan internet Anda telah kami terima.\n\n";
            $text .= "📅 Tanggal Bayar: $tglBayar\n";
            $text .= "💰 Nominal: Rp $nominal\n";
            $text .= "🗓️ Periode Tagihan: $periode\n";
            $text .= "✅ Status: LUNAS\n\n";

            // 2. MASUKKAN LINK KE PESAN
            $text .= "📄 *Unduh Invoice (PDF):*\n";
            $text .= "$linkDownload\n\n";

            $text .= "Internet Anda sudah aktif kembali. Terima kasih atas kepercayaan Anda.";

            $waResult = $this->wa->send($customer->phone, $text);
            $pesanWA = $waResult['status'] ? "WA Terkirim." : "WA Gagal.";
        }

        return back()->with('success', "Pembayaran sukses! $pesanMikrotik $pesanWA");
    }

    /**
     * BATALKAN PEMBAYARAN (KOREKSI + KIRIM WA)
     */
    public function cancelPayment($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);
        $customer = $invoice->customer;

        if ($invoice->status != 'paid')
            return back()->with('error', 'Gagal.');

        // Validasi Operator
        if (Auth::user()->role == 'operator') {
            if ($customer->operator_id != Auth::user()->id) {
                return back()->with('error', 'Akses Ditolak.');
            }
        }

        // Update Database
        $invoice->update(['status' => 'unpaid']);
        $customer->update(['is_active' => false]);

        // Eksekusi Mikrotik
        $userPppoe = $customer->pppoe_username;
        $pesanMikrotik = "";
        try {
            if ($this->mikrotik->isConnected()) {
                $this->mikrotik->setSecretStatus($userPppoe, 'disabled');
                $this->mikrotik->kickUser($userPppoe);
                $pesanMikrotik = "Mikrotik: Disabled.";
            }
        } catch (\Exception $e) {
            $pesanMikrotik = "Mikrotik Error: " . $e->getMessage();
        }

        // --- KIRIM NOTIFIKASI WA (PEMBATALAN) ---
        $pesanWA = "";
        if (!empty($customer->phone)) {
            $nominal = number_format($customer->monthly_price, 0, ',', '.');
            $periode = Carbon::parse($invoice->due_date)->locale('id')->isoFormat('MMMM Y');

            $text = "*PEMBATALAN STATUS LUNAS*\n\n";
            $text .= "Halo {$customer->name},\n";
            $text .= "Mohon maaf, terjadi koreksi pada sistem kami. Status pembayaran tagihan periode *$periode* (Rp $nominal) telah dibatalkan menjadi **BELUM LUNAS**.\n\n";
            $text .= "Koneksi internet untuk sementara dinonaktifkan.\n";
            $text .= "Silakan hubungi admin jika ini adalah kesalahan.";

            $waResult = $this->wa->send($customer->phone, $text);
            $pesanWA = $waResult['status'] ? "WA Terkirim." : "WA Gagal.";
        }

        return back()->with('warning', "Pembayaran DIBATALKAN! $pesanMikrotik $pesanWA");
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);

        if (Auth::user()->role == 'operator') {
            if ($invoice->customer->operator_id != Auth::user()->id)
                abort(403);
        }

        $invoice->delete();
        return back()->with('success', 'Invoice berhasil dihapus.');
    }

    public function print($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);
        if (Auth::user()->role == 'operator') {
            if ($invoice->customer->operator_id != Auth::user()->id)
                abort(403);
        }
        $company = Company::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('admin_id', $invoice->admin_id)
            ->first();

        // Convert Logo to Base64 (Optional for print, but keeps view logic simple)
        $logoBase64 = null;
        if ($company && !empty($company->logo_path)) {
            $path = public_path('uploads/' . $company->logo_path);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        return view('billing.invoice', compact('invoice', 'company', 'logoBase64'));
    }
}
