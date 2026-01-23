<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use App\Exports\CustomersExport;
use App\Imports\CustomersImport;
use App\Exports\CustomerTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    // 1. Halaman Utama Manajemen Pelanggan
    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedAdmin = $request->input('admin_id');

        $query = Customer::with(['operator', 'admin']);

        if ($user->role == 'operator') {
            $query->where('operator_id', $user->id);
        } elseif ($user->isSuperAdmin() && $selectedAdmin) {
            $query->where('admin_id', $selectedAdmin);
        }

        $customers = $query->get();
        $operators = User::where('role', 'operator')->get();
        
        $admins = [];
        if ($user->isSuperAdmin()) {
            $admins = User::where('role', 'admin')->get();
        }

        // Ambil profile dari Mikrotik untuk dropdown 'Tambah User'
        $profiles = [];
        try {
            if ($this->mikrotik->isConnected()) {
                $profiles = $this->mikrotik->getProfiles();
            }
        } catch (\Exception $e) {
            // Abaikan error koneksi agar halaman tetap jalan
        }

        return view('customers.index', compact('customers', 'profiles', 'operators', 'admins', 'selectedAdmin'));
    }

    // 2. Simpan User Baru (Ke DB & Mikrotik)
    public function store(Request $request)
    {
        $user = auth()->user();
        $plan = $user->plan;

        if (!$plan) {
            return back()->with('error', 'Layanan Anda belum diaktifkan (Belum memiliki paket).');
        }

        if (!$request->id) { // New customer
            $currentCount = Customer::count();
            if ($currentCount >= $plan->max_customers) {
                return back()->with('error', "Limit Pelanggan Tercapai! Paket Anda (" . $plan->name . ") hanya mendukung maksimal " . $plan->max_customers . " pelanggan.");
            }
        }

        $request->validate([
            'name' => 'required',
            'internet_number' => 'required|unique:customers,internet_number', // Validasi baru
            'pppoe_username' => 'required|unique:customers,pppoe_username',
            'pppoe_password' => 'required',
            'profile' => 'required',
            'monthly_price' => 'required|numeric',
        ]);

        try {
            // 1. Simpan ke Mikrotik (Username & Password tetap dipakai disini)
            $this->mikrotik->addSecret([
                'username' => $request->pppoe_username,
                'password' => $request->pppoe_password,
                'profile' => $request->profile,
                'comment' => $request->name . " (" . $request->internet_number . ")" // Opsional: Tambah No Inet di komentar mikrotik
            ]);

            // 2. Simpan ke Database
            Customer::create([
                'internet_number' => $request->internet_number, // Simpan No Internet
                'name' => $request->name,
                'phone' => $request->phone,
                'pppoe_username' => $request->pppoe_username,
                'pppoe_password' => $request->pppoe_password,
                'profile' => $request->profile,
                'monthly_price' => $request->monthly_price,
                'is_active' => true,
                'operator_id' => $request->operator_id,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'notes' => $request->notes,
            ]);

            return back()->with('success', 'Pelanggan berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // 3. Update Data Database (No HP / Harga)
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'internet_number' => 'required|unique:customers,internet_number,' . $id,
            'name' => 'required',
            'monthly_price' => 'required|numeric',
            'profile' => 'required', // Validasi Profile Baru
        ]);

        try {
            // 1. Update ke Mikrotik (Sinkronisasi Profile)
            // Kita hanya update profile, username/password biarkan tetap (kecuali mau fitur ganti pass juga)
            $this->mikrotik->updateSecret($customer->pppoe_username, [
                'profile' => $request->profile
            ]);

            // 2. Update Database Lokal
            $customer->update([
                'internet_number' => $request->internet_number,
                'name' => $request->name,
                'phone' => $request->phone,
                'monthly_price' => $request->monthly_price,
                'operator_id' => $request->operator_id,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'profile' => $request->profile,
                'notes' => $request->notes,
            ]);

            return back()->with('success', 'Data pelanggan & Paket Internet (Profile) berhasil diperbarui.');
        } catch (\Exception $e) {
            // Jika mikrotik mati, update DB saja tapi beri peringatan, atau gagalkan keduanya.
            // Disini kita gagalkan agar data tetap sinkron.
            return back()->with('error', 'Gagal update ke Mikrotik: ' . $e->getMessage());
        }
    }

    // 4. Hapus User (Dari DB & Mikrotik OPSIONAL)
    public function destroy(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        try {
            // Hapus di Mikrotik jika diminta (default true jika dari form lama tanpa flag, 
            // tapi kita akan ubah workflow di view untuk selalu kirim flag)
            $deleteMikrotik = $request->input('delete_mikrotik', '0') == '1';

            if ($deleteMikrotik) {
                $this->mikrotik->removeSecret($customer->pppoe_username);
            }

            // Hapus di DB
            $customer->delete();

            $msg = 'Pelanggan dihapus dari database.';
            if ($deleteMikrotik)
                $msg .= ' PPPoE Secret juga dihapus dari Mikrotik.';

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // --- LOGIKA SINKRONISASI (AJAX) ---

    // Tahap A: Ambil daftar username dari Mikrotik
    public function syncGetList()
    {
        try {
            $secrets = $this->mikrotik->getSecrets();
            // Kita hanya butuh data mentahnya untuk dikirim ke JS
            return response()->json([
                'status' => 'success',
                'data' => $secrets,
                'total' => count($secrets)
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function syncProcessItem(Request $request)
    {
        $secret = $request->secret;
        if (!$secret || !isset($secret['name'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid data'], 400);
        }

        $user = auth()->user();
        $admin = $user->isAdmin() ? $user : $user->parent;
        $plan = $admin->plan;

        $customer = Customer::where('admin_id', $admin->id)->where('pppoe_username', $secret['name'])->first();

        // Check Plan Limit (Total Pelanggan)
        if ($plan && $plan->max_customers > 0 && !$customer) {
            $currentCount = Customer::where('admin_id', $admin->id)->count();
            if ($currentCount >= $plan->max_customers) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Limit pelanggan ({$plan->max_customers}) tercapai. Sinkronisasi dihentikan.",
                    'stop' => true
                ], 403);
            }
        }

        if ($customer) {
            // Logic Update (Sama seperti sebelumnya)
            $customer->update([
                'pppoe_password' => $secret['password'] ?? '',
                'profile' => $secret['profile'] ?? 'default',
            ]);
            return response()->json(['status' => 'updated', 'name' => $secret['name']]);
        } else {
            // Logic Insert Baru (DIPERBARUI)

            // Generate 8 Digit Angka Acak untuk Nomor Internet
            // Loop while sederhana untuk memastikan benar-benar unik (opsional tapi disarankan)
            do {
                $randomInet = rand(10000000, 99999999);
            } while (Customer::where('internet_number', $randomInet)->exists());

            Customer::create([
                'internet_number' => $randomInet, // <-- Pakai angka acak 8 digit
                'name' => $secret['comment'] ?? $secret['name'],
                'pppoe_username' => $secret['name'],
                'pppoe_password' => $secret['password'] ?? '',
                'profile' => $secret['profile'] ?? 'default',
                'monthly_price' => 0,
                'is_active' => ($secret['disabled'] ?? 'false') == 'false'
            ]);

            return response()->json(['status' => 'created', 'name' => $secret['name']]);
        }
    }

    public function export()
    {
        return Excel::download(new CustomersExport, 'data_pelanggan.xlsx');
    }

    // 2. Import Data dari Excel
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new CustomersImport, $request->file('file'));
            return back()->with('success', 'Data pelanggan berhasil diimpor ke Database!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal impor: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new CustomerTemplateExport, 'template_import_pelanggan.xlsx');
    }

    // 10. Hapus Semua Pelanggan (Tenant-Aware)
    public function destroyAll(Request $request)
    {
        $user = auth()->user();
        $query = Customer::query();

        if ($user->role == 'operator') {
            $query->where('operator_id', $user->id);
        }

        $customers = $query->get();

        if ($customers->isEmpty()) {
            return back()->with('info', 'Tidak ada data pelanggan untuk dihapus.');
        }

        try {
            $failedMikrotik = [];
            $deleteMikrotik = $request->has('delete_mikrotik') && $request->delete_mikrotik == '1';

            if ($deleteMikrotik) {
                foreach ($customers as $customer) {
                    try {
                        $this->mikrotik->removeSecret($customer->pppoe_username);
                    } catch (\Exception $e) {
                        $failedMikrotik[] = $customer->pppoe_username;
                    }
                }
            }

            // Bulk delete database records
            $count = $customers->count();
            Customer::whereIn('id', $customers->pluck('id'))->delete();

            $msg = "Berhasil menghapus {$count} pelanggan dari database.";
            if ($deleteMikrotik) {
                if (empty($failedMikrotik)) {
                    $msg .= " Semua user juga berhasil dihapus dari Mikrotik.";
                } else {
                    $msg .= " Catatan: " . count($failedMikrotik) . " user gagal dihapus di Mikrotik.";
                }
            }

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal hapus massal: ' . $e->getMessage());
        }
    }
}
