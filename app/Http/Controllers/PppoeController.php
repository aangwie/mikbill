<?php

namespace App\Http\Controllers;

use App\Services\MikrotikService;
use App\Models\RouterSetting; // 1. Import Model RouterSetting
use Illuminate\Http\Request;

class PppoeController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotik = $mikrotikService;
    }

    public function index()
    {
        // 2. Ambil Info Router dari Database
        $routerInfo = RouterSetting::where('is_active', true)->first();
        // Fallback: Jika tidak ada yang aktif (misal baru install), ambil yang pertama
        if (!$routerInfo) {
            $routerInfo = RouterSetting::first();
        }
        
        // 3. Cek Status Koneksi
        $isConnected = $this->mikrotik->isConnected();

        // Jika belum ada settingan sama sekali
        if (!$routerInfo) {
            return view('pppoe.index', [
                'routerInfo' => null,
                'isConnected' => false,
                'secrets' => [],
                'actives' => collect([]),
                'error' => 'Konfigurasi Router belum diatur. Silakan ke menu Pengaturan -> Konfigurasi Mikrotik.'
            ]);
        }

        // Jika setting ada tapi Gagal Konek
        if (!$isConnected) {
            return view('pppoe.index', [
                'routerInfo' => $routerInfo,
                'isConnected' => false,
                'secrets' => [],
                'actives' => collect([]),
                'error' => "Gagal terhubung ke Mikrotik ({$routerInfo->host}:{$routerInfo->port}). Cek koneksi/VPN."
            ]);
        }

        try {
            // Ambil data dari Mikrotik
            $activeUsers = $this->mikrotik->getActiveUsers();
            $secrets = $this->mikrotik->getSecrets();

            // Mapping data active user
            $activeCollection = collect($activeUsers)->keyBy('name');

            return view('pppoe.index', [
                'routerInfo' => $routerInfo, // Kirim data router ke view
                'isConnected' => true,       // Kirim status koneksi
                'secrets' => $secrets,
                'actives' => $activeCollection,
                'error' => null
            ]);

        } catch (\Exception $e) {
            return view('pppoe.index', [
                'routerInfo' => $routerInfo,
                'isConnected' => false,
                'secrets' => [],
                'actives' => collect([]),
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // ... method kick & toggle biarkan tetap sama ...
    public function kick(Request $request)
    {
        // ... kode lama ...
        $request->validate(['username' => 'required']);
        try {
            $status = $this->mikrotik->kickUser($request->username);
            if ($status) return back()->with('success', "User {$request->username} berhasil diputus (Kick).");
            else return back()->with('warning', "User {$request->username} tidak sedang online.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal melakukan kick: ' . $e->getMessage());
        }
    }

    public function toggle(Request $request)
    {
        // ... kode lama ...
        $request->validate(['username' => 'required', 'action' => 'required|in:enable,disable']);
        try {
            if ($request->action === 'disable') {
                $this->mikrotik->setSecretStatus($request->username, 'disabled');
                $this->mikrotik->kickUser($request->username);
                $msg = "User {$request->username} dinonaktifkan & dikick.";
            } else {
                $this->mikrotik->setSecretStatus($request->username, 'enabled');
                $msg = "User {$request->username} diaktifkan.";
            }
            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}