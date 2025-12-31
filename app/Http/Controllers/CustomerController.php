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
    public function index()
    {
        $user = auth()->user();

        $query = Customer::with('operator');

        if ($user->role == 'operator') {
            $query->where('operator_id', $user->id);
        }

        $customers = $query->get();
        $operators = User::where('role', 'operator')->get();

        // Ambil profile dari Mikrotik untuk dropdown 'Tambah User'
        $profiles = [];
        try {
            if ($this->mikrotik->isConnected()) {
                $profiles = $this->mikrotik->getProfiles();
            }
        } catch (\Exception $e) {
            // Abaikan error koneksi agar halaman tetap jalan
        }

        return view('customers.index', compact('customers', 'profiles', 'operators'));
    }

    // 2. Simpan User Baru (Ke DB & Mikrotik)
    public function store(Request $request)
    {
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

    // 4. Hapus User (Dari DB & Mikrotik)
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        try {
            // Hapus di Mikrotik
            $this->mikrotik->removeSecret($customer->pppoe_username);

            // Hapus di DB
            $customer->delete();

            return back()->with('success', 'Pelanggan dihapus permanen.');
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

    // Tahap B: Proses simpan per-item (dipanggil berulang oleh JS)
    public function syncProcessItem(Request $request)
    {
        $secret = $request->secret;

        $customer = Customer::where('pppoe_username', $secret['name'])->first();

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
}
