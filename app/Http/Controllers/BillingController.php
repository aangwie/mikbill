<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Company;
use App\Models\User;
use App\Models\CustomerBalance;
use App\Services\MikrotikService;
use App\Services\WhatsappService;
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
        $selectedAdminId = $request->input('admin_id');

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
        } elseif ($user->role == 'superadmin' && $selectedAdminId) {
            $invoiceQuery->whereHas('customer', function ($q) use ($selectedAdminId) {
                $q->where('admin_id', $selectedAdminId);
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

        // Hitung Piutang (total outstanding dari semua invoice unpaid sebelum bulan ini)
        $piutangQuery = Invoice::where('status', 'unpaid')
            ->where(function($q) use ($month, $year) {
                $q->whereYear('due_date', '<', $year)
                  ->orWhere(function($q2) use ($month, $year) {
                      $q2->whereYear('due_date', $year)->whereMonth('due_date', '<', $month);
                  });
            });
        if ($user->role == 'operator') {
            $piutangQuery->whereHas('customer', fn($q) => $q->where('operator_id', $user->id));
        } elseif ($user->role == 'superadmin' && $selectedAdminId) {
            $piutangQuery->whereHas('customer', fn($q) => $q->where('admin_id', $selectedAdminId));
        }
        $total_piutang = $piutangQuery->sum('outstanding');
        // Juga tambahkan invoice yang masih unpaid sepenuhnya (outstanding = 0 tapi belum dibayar)
        $piutangFullUnpaid = (clone $piutangQuery)->where('outstanding', 0)->get();
        foreach ($piutangFullUnpaid as $pInv) {
            $total_piutang += ($pInv->price > 0 ? $pInv->price : ($pInv->customer->monthly_price ?? 0));
        }

        $customerQuery = Customer::orderBy('name', 'asc');
        if ($user->role == 'operator') {
            $customerQuery->where('operator_id', $user->id);
        }
        $customers = $customerQuery->get();

        $admins = [];
        if ($user->role == 'superadmin') {
            $admins = User::whereIn('role', ['admin', 'superadmin'])->get(['id', 'name', 'role']);
        }

        return view('billing.index', compact('invoices', 'customers', 'month', 'year', 'total_bill', 'paid_bill', 'unpaid_bill', 'total_piutang', 'admins', 'selectedAdminId'));
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
                // Hitung total outstanding dari invoice sebelumnya yang belum lunas
                $prevOutstanding = Invoice::where('customer_id', $customer->id)
                    ->where('status', 'unpaid')
                    ->where(function($q) use ($request) {
                        $q->whereYear('due_date', '<', $request->year)
                          ->orWhere(function($q2) use ($request) {
                              $q2->whereYear('due_date', $request->year)
                                 ->whereMonth('due_date', '<', $request->month);
                          });
                    })
                    ->sum('outstanding');

                Invoice::create([
                    'customer_id' => $customer->id,
                    'admin_id' => $customer->admin_id,
                    'due_date' => $request->due_date,
                    'price' => $customer->monthly_price,
                    'outstanding' => $prevOutstanding,
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
        } elseif ($user->role == 'superadmin' && $request->has('admin_id')) {
            $query->where('admin_id', $request->admin_id);
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
        // Superadmin is allowed to process any if needed, or we could add a validation for selected admin_id here if passed.
        if (Auth::user()->role == 'superadmin' && $request->has('admin_id') && $customer->admin_id != $request->admin_id) {
            return response()->json(['status' => 'error', 'message' => 'Admin ID mismatch'], 403);
        }

        $exists = Invoice::where('customer_id', $customer->id)
            ->where('status', 'unpaid')
            ->whereMonth('due_date', $request->month)
            ->whereYear('due_date', $request->year)
            ->exists();

        if ($exists) {
            return response()->json(['status' => 'skipped', 'name' => $customer->name]);
        }

        // Hitung outstanding dari bulan sebelumnya
        $prevOutstanding = Invoice::where('customer_id', $customer->id)
            ->where('status', 'unpaid')
            ->where(function($q) use ($request) {
                $q->whereYear('due_date', '<', $request->year)
                  ->orWhere(function($q2) use ($request) {
                      $q2->whereYear('due_date', $request->year)
                         ->whereMonth('due_date', '<', $request->month);
                  });
            })
            ->sum('outstanding');

        Invoice::create([
            'customer_id' => $customer->id,
            'admin_id' => $customer->admin_id,
            'due_date' => $request->due_date,
            'price' => $customer->monthly_price,
            'outstanding' => $prevOutstanding,
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
     * PROCESS PAYMENT VIA AJAX (MASS PAYMENT)
     */
    public function processPaymentAjax($id)
    {
        try {
            $invoice = Invoice::with('customer')->findOrFail($id);

            // Skip if already paid
            if ($invoice->status == 'paid') {
                return response()->json([
                    'status' => 'skipped',
                    'message' => 'Invoice sudah lunas.',
                    'customer' => $invoice->customer->name
                ]);
            }

            $customer = $invoice->customer;
            $userPppoe = $customer->pppoe_username;

            // Validasi Operator
            if (Auth::user()->role == 'operator') {
                if ($customer->operator_id != Auth::user()->id) {
                    return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
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
                    // $this->mikrotik->kickUser($userPppoe); // Disable kick active connection
                    $pesanMikrotik = "Mikrotik: Enabled.";
                } else {
                    $pesanMikrotik = "Mikrotik: Gagal Konek.";
                }
            } catch (\Exception $e) {
                // Log error but don't fail the payment
                $pesanMikrotik = "Mikrotik Error.";
            }

            // --- KIRIM NOTIFIKASI WA (LUNAS) ---
            $pesanWA = "";
            try {
                if (!empty($customer->phone)) {
                    $tglBayar = Carbon::now()->locale('id')->isoFormat('D MMMM Y, HH:mm');
                    $nominal = number_format($customer->monthly_price, 0, ',', '.');
                    $periode = Carbon::parse($invoice->due_date)->locale('id')->isoFormat('MMMM Y');
                    $linkDownload = route('frontend.invoice', $invoice->id);

                    $text = "*PEMBAYARAN DITERIMA*\n\n";
                    $text .= "Halo {$customer->name},\n";
                    $text .= "Terima kasih, pembayaran tagihan internet Anda telah kami terima.\n\n";
                    $text .= "📅 Tanggal Bayar: $tglBayar\n";
                    $text .= "💰 Nominal: Rp $nominal\n";
                    $text .= "🗓️ Periode Tagihan: $periode\n";
                    $text .= "✅ Status: LUNAS\n\n";
                    $text .= "📄 *Unduh Invoice (PDF):*\n";
                    $text .= "$linkDownload\n\n";
                    $text .= "Internet Anda sudah aktif kembali. Terima kasih atas kepercayaan Anda.";

                    $waResult = $this->wa->send($customer->phone, $text);
                    $pesanWA = $waResult['status'] ? "WA Terkirim." : "WA Gagal.";
                }
            } catch (\Exception $e) {
                $pesanWA = "WA Error.";
            }

            return response()->json([
                'status' => 'success',
                'customer' => $customer->name,
                'message' => "Sukses. $pesanMikrotik $pesanWA"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'customer' => 'Unknown'
            ], 500);
        }
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
                // $this->mikrotik->kickUser($userPppoe); // Disable kick active connection
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
     * PROSES PEMBAYARAN MANUAL VIA AJAX (SweetAlert)
     * Supports: manual payment amount & saldo payment
     */
    public function payManual(Request $request, $id)
    {
        $request->validate([
            'method' => 'required|in:manual,saldo',
            'amount' => 'nullable|numeric|min:0',
            'additional_payments' => 'nullable|array',
            'additional_payments.*.invoice_id' => 'sometimes|integer',
            'additional_payments.*.amount' => 'sometimes|numeric|min:0',
        ]);

        $invoice = Invoice::with('customer')->findOrFail($id);
        $customer = $invoice->customer;

        if ($invoice->status == 'paid') {
            return response()->json(['success' => false, 'message' => 'Invoice sudah lunas.']);
        }

        $invoiceDate = \Carbon\Carbon::parse($invoice->due_date);
        $month = $invoiceDate->month;
        $year = $invoiceDate->year;

        $previousInvoices = Invoice::where('customer_id', $invoice->customer_id)
            ->where('status', 'unpaid')
            ->where('id', '!=', $invoice->id)
            ->where(function($q) use ($month, $year) {
                $q->whereYear('due_date', '<', $year)
                  ->orWhere(function($q2) use ($month, $year) {
                      $q2->whereYear('due_date', $year)->whereMonth('due_date', '<', $month);
                  });
            })
            ->get();

        $akumulasiKurangBayar = 0;
        foreach ($previousInvoices as $prevInv) {
            $prevPrice = $prevInv->price > 0 ? $prevInv->price : ($prevInv->customer->monthly_price ?? 0);
            if ($prevPrice == 0) continue; 
            
            $unpaid = $prevPrice - $prevInv->paid_amount;
            $akumulasiKurangBayar += $unpaid;
        }
        if ($akumulasiKurangBayar < 0) $akumulasiKurangBayar = 0;

        // Sinkronisasi outstanding real-time ke database
        $invoice->outstanding = $akumulasiKurangBayar;
        $invoice->save();

        $price = $invoice->price > 0 ? $invoice->price : ($customer->monthly_price ?? 0);
        // Total yang harus dibayar = price saja (tanpa tunggakan, karena tunggakan dibayar terpisah via additional_payments)
        $mainDue = $price;

        // Hitung total additional payments
        $additionalPayments = $request->input('additional_payments', []);
        $totalAdditionalAmount = 0;
        foreach ($additionalPayments as $ap) {
            $totalAdditionalAmount += (int) $ap['amount'];
        }

        $method = $request->method;
        $payAmount = 0;

        if ($method == 'saldo') {
            // Bayar pakai saldo
            $totalAll = $mainDue + $totalAdditionalAmount;
            $saldo = CustomerBalance::where('customer_id', $customer->id)->sum('amount');
            if ($saldo <= 0) {
                return response()->json(['success' => false, 'message' => 'Saldo pelanggan kosong.']);
            }
            $payAmount = min($saldo, $mainDue);

            // Kurangi saldo
            CustomerBalance::create([
                'customer_id' => $customer->id,
                'amount' => -min($saldo, $totalAll),
                'description' => 'Pembayaran Invoice #INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT),
            ]);
        } else {
            // Bayar manual
            $payAmount = (int) $request->amount;
            if ($payAmount <= 0) {
                return response()->json(['success' => false, 'message' => 'Jumlah pembayaran harus lebih dari 0.']);
            }
        }

        // === Proses pembayaran tambahan untuk bulan sebelumnya ===
        $additionalMessages = [];
        foreach ($additionalPayments as $ap) {
            $addInv = Invoice::find($ap['invoice_id']);
            if (!$addInv || $addInv->customer_id != $customer->id) continue;
            if ($addInv->status == 'paid') continue;

            $addPrice = $addInv->price > 0 ? $addInv->price : ($customer->monthly_price ?? 0);
            $addDue = $addPrice - $addInv->paid_amount;
            $addPay = min((int) $ap['amount'], $addDue);

            if ($addPay <= 0) continue;

            $newPaid = $addInv->paid_amount + $addPay;
            if ($newPaid >= $addPrice) {
                $addInv->update([
                    'status' => 'paid',
                    'paid_amount' => $addPrice,
                    'outstanding' => 0,
                ]);
                $additionalMessages[] = Carbon::parse($addInv->due_date)->locale('id')->isoFormat('MMM Y') . ': Lunas';
            } else {
                $addInv->update([
                    'paid_amount' => $newPaid,
                ]);
                $additionalMessages[] = Carbon::parse($addInv->due_date)->locale('id')->isoFormat('MMM Y') . ': Rp ' . number_format($addPay, 0, ',', '.');
            }
        }

        // === Proses pembayaran invoice utama ===
        if ($payAmount >= $mainDue) {
            // Lunas atau lebih
            $excess = $payAmount - $mainDue;
            $invoice->update([
                'status' => 'paid',
                'paid_amount' => $mainDue,
                'outstanding' => 0,
            ]);

            // Kelebihan masuk ke saldo
            if ($excess > 0) {
                CustomerBalance::create([
                    'customer_id' => $customer->id,
                    'amount' => $excess,
                    'description' => 'Kelebihan bayar Invoice #INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT),
                ]);
            }

            // Aktifkan di Mikrotik
            $customer->update(['is_active' => true]);
            try {
                if ($this->mikrotik->isConnected()) {
                    $this->mikrotik->setSecretStatus($customer->pppoe_username, 'enabled');
                }
            } catch (\Exception $e) { /* ignore */ }

            $msg = 'Invoice LUNAS!';
            if ($excess > 0) {
                $msg .= ' Kelebihan Rp ' . number_format($excess, 0, ',', '.') . ' masuk ke saldo.';
            }
            if (!empty($additionalMessages)) {
                $msg .= ' Tunggakan: ' . implode(', ', $additionalMessages) . '.';
            }

            return response()->json(['success' => true, 'message' => $msg, 'status' => 'paid']);
        } else {
            // Kurang bayar
            $remaining = $mainDue - $payAmount;
            $invoice->update([
                'paid_amount' => $payAmount,
                'outstanding' => $remaining,
                // status tetap unpaid
            ]);

            $msg = 'Pembayaran sebagian diterima. Kurang bayar bulan ini: Rp ' . number_format($remaining, 0, ',', '.');
            if (!empty($additionalMessages)) {
                $msg .= ' Tunggakan: ' . implode(', ', $additionalMessages) . '.';
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'status' => 'partial',
            ]);
        }
    }

    /**
     * BATALKAN PEMBAYARAN (KOREKSI + KIRIM WA)
     */
    public function cancelPayment($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);
        $customer = $invoice->customer;

        if ($invoice->status != 'paid' && $invoice->paid_amount <= 0) {
            if (request()->ajax()) return response()->json(['success' => false, 'message' => 'Gagal. Invoice tidak memiliki pembayaran yang dapat dibatalkan.']);
            return back()->with('error', 'Gagal.');
        }

        // Validasi Operator
        if (Auth::user()->role == 'operator') {
            if ($customer->operator_id != Auth::user()->id) {
                if (request()->ajax()) return response()->json(['success' => false, 'message' => 'Akses Ditolak.']);
                return back()->with('error', 'Akses Ditolak.');
            }
        }

        // Hitung ulang outstanding (akumulasi kurang bayar sebelum invoice ini)
        $invoiceDate = \Carbon\Carbon::parse($invoice->due_date);
        $month = $invoiceDate->month;
        $year = $invoiceDate->year;

        $previousInvoices = Invoice::where('customer_id', $invoice->customer_id)
            ->where('status', 'unpaid')
            ->where('id', '!=', $invoice->id)
            ->where(function($q) use ($month, $year) {
                $q->whereYear('due_date', '<', $year)
                  ->orWhere(function($q2) use ($month, $year) {
                      $q2->whereYear('due_date', $year)->whereMonth('due_date', '<', $month);
                  });
            })
            ->get();

        $akumulasiKurangBayar = 0;
        foreach ($previousInvoices as $prevInv) {
            $prevPrice = $prevInv->price > 0 ? $prevInv->price : ($prevInv->customer->monthly_price ?? 0);
            if ($prevPrice == 0) continue; 
            
            $unpaid = $prevPrice - $prevInv->paid_amount;
            $akumulasiKurangBayar += $unpaid;
        }
        if ($akumulasiKurangBayar < 0) $akumulasiKurangBayar = 0;

        // Update Database
        $invoice->update([
            'status' => 'unpaid',
            'paid_amount' => 0,
            'outstanding' => $akumulasiKurangBayar
        ]);
        
        $customer->update(['is_active' => false]);

        // Eksekusi Mikrotik
        $userPppoe = $customer->pppoe_username;
        $pesanMikrotik = "";
        try {
            if ($this->mikrotik->isConnected()) {
                $this->mikrotik->setSecretStatus($userPppoe, 'disabled');
                // $this->mikrotik->kickUser($userPppoe); // Disable kick active connection
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

        $msg = "Pembayaran DIBATALKAN! $pesanMikrotik $pesanWA";
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg
            ]);
        }

        return back()->with('warning', $msg);
    }

    /**
     * AJAX: Get unpaid months for a specific invoice's customer (same year, prior months)
     */
    public function getUnpaidMonths($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);
        $invoiceDate = Carbon::parse($invoice->due_date);
        $month = $invoiceDate->month;
        $year = $invoiceDate->year;

        $unpaidInvoices = Invoice::where('customer_id', $invoice->customer_id)
            ->where('status', 'unpaid')
            ->where('id', '!=', $invoice->id)
            ->where(function($q) use ($month, $year) {
                $q->whereYear('due_date', $year)
                  ->whereMonth('due_date', '<', $month);
            })
            ->orderBy('due_date', 'asc')
            ->get();

        $data = [];
        foreach ($unpaidInvoices as $inv) {
            $price = $inv->price > 0 ? $inv->price : ($inv->customer->monthly_price ?? 0);
            if ($price == 0) continue;
            
            $kurangBayar = $price - $inv->paid_amount;
            if ($kurangBayar <= 0) continue;

            $data[] = [
                'id' => $inv->id,
                'periode' => Carbon::parse($inv->due_date)->locale('id')->isoFormat('MMMM Y'),
                'tagihan' => $price,
                'dibayar' => $inv->paid_amount,
                'kurang_bayar' => $kurangBayar,
            ];
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * AJAX: Get payment history for a customer
     */
    public function customerHistory($id)
    {
        $invoices = Invoice::where('customer_id', $id)
            ->orderBy('due_date', 'desc')
            ->get();

        $history = [];
        $totalTunggakan = 0;

        foreach ($invoices as $inv) {
            $price = $inv->price > 0 ? $inv->price : ($inv->customer->monthly_price ?? 0);
            
            $status = $inv->status;
            if ($price == 0) {
                $status = 'paid';
            }

            $unpaid = 0;
            if ($status == 'unpaid') {
                $unpaid = $price - $inv->paid_amount;
                $totalTunggakan += $unpaid;
            }

            $history[] = [
                'id' => $inv->id,
                'no_invoice' => '#INV-' . str_pad($inv->id, 5, '0', STR_PAD_LEFT),
                'periode' => Carbon::parse($inv->due_date)->locale('id')->isoFormat('MMMM Y'),
                'tagihan' => $price,
                'dibayar' => $status == 'paid' ? ($inv->paid_amount > 0 ? $inv->paid_amount : $price) : $inv->paid_amount,
                'kurang' => $unpaid,
                'status' => $status,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $history,
            'total_tunggakan' => $totalTunggakan
        ]);
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

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'type' => 'required|in:selected,all',
            'ids' => 'nullable|array',
            'ids.*' => 'exists:invoices,id',
            'month' => 'nullable|numeric',
            'year' => 'nullable|numeric',
        ]);

        $user = Auth::user();
        $count = 0;

        if ($request->type == 'selected') {
            if (!$request->ids) {
                return back()->with('error', 'Tidak ada tagihan yang dipilih.');
            }

            $invoices = Invoice::whereIn('id', $request->ids)->get();
            foreach ($invoices as $inv) {
                // Permission Check
                if ($user->role == 'operator' && $inv->customer->operator_id != $user->id)
                    continue;
                if ($user->role == 'admin' && $inv->admin_id != $user->id)
                    continue;

                $inv->delete();
                $count++;
            }

        } else {
            // Delete ALL for Month/Year
            if (!$request->month || !$request->year) {
                return back()->with('error', 'Parameter bulan/tahun tidak valid.');
            }

            $query = Invoice::whereMonth('due_date', $request->month)
                ->whereYear('due_date', $request->year);

            if ($user->role == 'operator') {
                $query->whereHas('customer', function ($q) use ($user) {
                    $q->where('operator_id', $user->id);
                });
            }

            // Global Scope handles Admin/Superadmin generally, but explicit check doesn't hurt OR if using TenantScope
            // Assuming TenantScope handles 'admin' filtering automatically.

            $count = $query->delete();
        }

        return back()->with('success', "Berhasil menghapus $count tagihan.");
    }

    public function print($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);
        if (Auth::user()->role == 'operator') {
            if ($invoice->customer->operator_id != Auth::user()->id)
                abort(403);
        }
        // Get company based on authenticated user's role
        $user = Auth::user();
        $companyAdminId = $user->isOperator() ? $user->parent_id : $user->id;
        $company = Company::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('admin_id', $companyAdminId)
            ->first();

        // Fallback to superadmin's company if admin has missing data
        $fallbackCompany = null;
        if (!$user->isSuperAdmin()) {
            $fallbackCompany = Company::withoutGlobalScope(\App\Scopes\TenantScope::class)
                ->whereHas('admin', function ($q) {
                    $q->where('role', 'superadmin');
                })
                ->first();
        }

        // Per-field fallback: admin's own data → superadmin's data → default
        $logoSource = ($company && !empty($company->logo_path)) ? $company : $fallbackCompany;
        $companyName = (!empty($company->company_name) ? $company->company_name : null)
            ?? ($fallbackCompany->company_name ?? 'BillNesia');
        $companyAddress = (!empty($company->address) ? $company->address : null)
            ?? ($fallbackCompany->address ?? '');
        $companyPhone = (!empty($company->phone) ? $company->phone : null)
            ?? ($fallbackCompany->phone ?? '');
        $companyEmail = (!empty($company->email) ? $company->email : null)
            ?? ($fallbackCompany->email ?? '');

        // Convert Logo to Base64 using Storage disk (works on shared hosting)
        $logoBase64 = null;
        $hostingDisk = \Illuminate\Support\Facades\Storage::disk('hosting');
        if ($logoSource && !empty($logoSource->logo_path)) {
            if ($hostingDisk->exists($logoSource->logo_path)) {
                $type = pathinfo($logoSource->logo_path, PATHINFO_EXTENSION);
                $data = $hostingDisk->get($logoSource->logo_path);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        // If still no logo, use BillNesia default logo
        if (!$logoBase64) {
            $billnesiaPath = rtrim($hostingDisk->path(''), '/\\') . '/../img/billnesia_logo.png';
            if (file_exists($billnesiaPath)) {
                $data = file_get_contents($billnesiaPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($data);
            }
        }

        // Hitung akumulasi kurang bayar dari bulan-bulan sebelumnya
        $invoiceDate = \Carbon\Carbon::parse($invoice->due_date);
        $month = $invoiceDate->month;
        $year = $invoiceDate->year;

        $previousInvoices = Invoice::where('customer_id', $invoice->customer_id)
            ->where('status', 'unpaid')
            ->where('id', '!=', $invoice->id)
            ->where(function($q) use ($month, $year) {
                $q->whereYear('due_date', '<', $year)
                  ->orWhere(function($q2) use ($month, $year) {
                      $q2->whereYear('due_date', $year)->whereMonth('due_date', '<', $month);
                  });
            })
            ->get();

        $akumulasiKurangBayar = 0;
        foreach ($previousInvoices as $prevInv) {
            $prevPrice = $prevInv->price > 0 ? $prevInv->price : ($prevInv->customer->monthly_price ?? 0);
            // Jika tagihan 0, anggap lunas (tidak ada kurang bayar)
            if ($prevPrice == 0) continue; 
            
            $unpaid = $prevPrice - $prevInv->paid_amount;
            $akumulasiKurangBayar += $unpaid;
        }

        // Pastikan tidak negatif jika ada anomali data
        if ($akumulasiKurangBayar < 0) $akumulasiKurangBayar = 0;

        return view('billing.invoice', compact(
            'invoice',
            'company',
            'logoBase64',
            'companyName',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'akumulasiKurangBayar'
        ));
    }

    public function bulkUpdateDueDate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:invoices,id',
            'due_date' => 'required|date',
        ]);

        $user = Auth::user();
        $count = 0;

        $invoices = Invoice::whereIn('id', $request->ids)->get();
        foreach ($invoices as $inv) {
            // Permission Check
            if ($user->role == 'operator' && $inv->customer->operator_id != $user->id)
                continue;
            if ($user->role == 'admin' && $inv->admin_id != $user->id)
                continue;

            $inv->update(['due_date' => $request->due_date]);
            $count++;
        }

        return back()->with('success', "Berhasil memperbarui jatuh tempo untuk $count tagihan.");
    }
}
