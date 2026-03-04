<?php

namespace App\Http\Controllers;

use App\Models\RouterSetting;
use Illuminate\Http\Request;

use App\Models\Plan;

class RouterSettingController extends Controller
{
    public function index(Request $request)
    {
        $ownership = $request->input('ownership', 'semua');
        // Jika superadmin, bypass SEMUA global scope agar bisa melihat semua router
        if (auth()->user()->isSuperAdmin()) {
            $query = RouterSetting::withoutGlobalScopes()->orderBy('is_active', 'desc');

            if ($ownership === 'superadmin') {
                $query->whereHas('admin', function ($q) {
                    $q->where('role', 'superadmin');
                });
            } elseif ($ownership === 'admin') {
                $query->whereHas('admin', function ($q) {
                    $q->where('role', 'admin');
                });
            }
        } else {
            $query = RouterSetting::orderBy('is_active', 'desc');
        }

        $routers = $query->with('admin')->get(); // Eager load admin info
        $plans = Plan::all();
        return view('router.index', compact('routers', 'plans', 'ownership'));
    }

    // SIMPAN BARU / UPDATE
    public function store(Request $request)
    {
        $user = auth()->user();
        // Superadmin bypasses plan and activation checks
        if (!$user->isSuperAdmin()) {
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

            // Restriction: Only activated users can add routers
            if (!$request->id && !$user->is_activated) {
                return back()->with('error', 'Akun Admin Anda belum diaktifkan oleh Superadmin untuk menambah router.');
            }
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
            $router = $user->isSuperAdmin()
                ? RouterSetting::withoutGlobalScopes()->find($request->id)
                : RouterSetting::find($request->id);

            $router->update($data);
            $msg = 'Konfigurasi berhasil diperbarui.';
        } else {
            // Jika ini router pertama untuk user ini, langsung set aktif
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
        $user = auth()->user();

        $router = $user->isSuperAdmin()
            ? RouterSetting::withoutGlobalScope(\App\Scopes\TenantScope::class)->find($id)
            : RouterSetting::find($id);

        if (!$router) {
            return back()->with('error', 'Router tidak ditemukan.');
        }

        // 1. Matikan semua router milik admin_id yang sama dengan router yang akan diaktifkan
        // Ini memastikan aktivasi router superadmin tidak mematikan router admin, dan sebaliknya.
        RouterSetting::withoutGlobalScopes()
            ->where('admin_id', $router->admin_id)
            ->update(['is_active' => false]);

        // 2. Aktifkan yang dipilih
        $router->update(['is_active' => true]);

        return back()->with('success', "Berhasil beralih ke router: {$router->label} ({$router->host})");
    }

    // HAPUS ROUTER
    public function destroy($id)
    {
        $user = auth()->user();
        $router = $user->isSuperAdmin()
            ? RouterSetting::withoutGlobalScopes()->find($id)
            : RouterSetting::find($id);

        if ($router->is_active) {
            return back()->with('error', 'Tidak bisa menghapus router yang sedang digunakan (Aktif). Pindahkan koneksi dulu.');
        }

        $router->delete();
        return back()->with('success', 'Data konfigurasi router dihapus.');
    }
}