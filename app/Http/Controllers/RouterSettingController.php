<?php

namespace App\Http\Controllers;

use App\Models\RouterSetting;
use Illuminate\Http\Request;

use App\Models\Plan;

class RouterSettingController extends Controller
{
    public function index()
    {
        // Ambil semua data router
        $routers = RouterSetting::orderBy('is_active', 'desc')->get(); // Yang aktif ditaruh paling atas
        $plans = Plan::all();
        return view('router.index', compact('routers', 'plans'));
    }

    // SIMPAN BARU / UPDATE
    public function store(Request $request)
    {
        $user = auth()->user();
        $plan = $user->plan;

        // Jika tidak punya paket (null), kita beri default limit atau paksa punya?
        // Untuk amannya, jika null asumsikan starter (tapi lebih baik dibuat via seeder/default)
        if (!$plan) {
            return back()->with('error', 'Silakan hubungi Superadmin untuk aktivasi paket layanan Anda.');
        }

        if (!$request->id) { // Jika INSERT baru
            $currentCount = RouterSetting::count();
            if ($currentCount >= $plan->max_routers) {
                return back()->with('error', "Limit Router Tercapai! Paket Anda (" . $plan->name . ") hanya mendukung maksimal " . $plan->max_routers . " router.");
            }
        }

        $request->validate([
            'label' => 'required',
            'host' => 'required',
            'username' => 'required',
            'port' => 'required|numeric',
        ]);

        // Restriction: Only activated users can add routers
        if (!$request->id && !auth()->user()->is_activated) {
            return back()->with('error', 'Akun Admin Anda belum diaktifkan oleh Superadmin untuk menambah router.');
        }

        $data = [
            'label' => $request->label,
            'host' => $request->host,
            'username' => $request->username,
            'port' => $request->port,
        ];

        // Jika password diisi, update. Jika kosong, biarkan password lama (khusus edit).
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        // Cek ID (jika ada ID berarti Edit, jika tidak berarti Baru)
        if ($request->id) {
            $router = RouterSetting::find($request->id);
            $router->update($data);
            $msg = 'Konfigurasi berhasil diperbarui.';
        } else {
            // Jika ini router pertama, langsung set aktif
            if (RouterSetting::count() == 0) {
                $data['is_active'] = true;
            }
            $data['password'] = $request->password; // Password wajib buat baru
            RouterSetting::create($data);
            $msg = 'Router baru berhasil ditambahkan.';
        }

        return back()->with('success', $msg);
    }

    // AKTIFKAN ROUTER (GUNAKAN)
    public function activate($id)
    {
        // 1. Matikan semua dulu
        RouterSetting::query()->update(['is_active' => false]);

        // 2. Aktifkan yang dipilih
        $router = RouterSetting::find($id);
        $router->update(['is_active' => true]);

        return back()->with('success', "Berhasil beralih ke router: {$router->label} ({$router->host})");
    }

    // HAPUS ROUTER
    public function destroy($id)
    {
        $router = RouterSetting::find($id);

        if ($router->is_active) {
            return back()->with('error', 'Tidak bisa menghapus router yang sedang digunakan (Aktif). Pindahkan koneksi dulu.');
        }

        $router->delete();
        return back()->with('success', 'Data konfigurasi router dihapus.');
    }
}