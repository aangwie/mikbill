<?php

namespace App\Http\Controllers;

use App\Services\MikrotikService;
use Illuminate\Http\Request;

class PppoeController extends Controller
{
    protected $mikrotik;

    // Inject Service ke Controller
    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    public function index()
    {
        // Cek koneksi dulu
        if (!$this->mikrotik->isConnected()) {
            return view('pppoe.index', ['error' => 'Gagal terhubung ke Mikrotik (223.23.23.1:7787). Cek koneksi/VPN.']);
        }

        try {
            // Ambil data
            $activeUsers = $this->mikrotik->getActiveUsers();
            $secrets = $this->mikrotik->getSecrets();

            // Kita mapping agar mudah diakses di view
            // Mengubah array activeUsers menjadi collection keyed by name untuk pencarian cepat
            $activeCollection = collect($activeUsers)->keyBy('name');

            return view('pppoe.index', [
                'secrets' => $secrets,
                'actives' => $activeCollection,
                'error' => null
            ]);

        } catch (\Exception $e) {
            return view('pppoe.index', ['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function kick(Request $request)
    {
        $request->validate(['username' => 'required']);

        try {
            $status = $this->mikrotik->kickUser($request->username);

            if ($status) {
                return back()->with('success', "User {$request->username} berhasil diputus (Kick).");
            } else {
                return back()->with('warning', "User {$request->username} tidak sedang online.");
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal melakukan kick: ' . $e->getMessage());
        }
    }

    // ... method index dan kick yang sudah ada ...

    // Method BARU untuk Enable/Disable User
    public function toggle(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'action' => 'required|in:enable,disable' // Hanya terima 'enable' atau 'disable'
        ]);

        $username = $request->username;
        $action = $request->action;

        try {
            if ($action === 'disable') {
                // LOGIKA 1: Disable Secret (Blokir Akun)
                $this->mikrotik->setSecretStatus($username, 'disabled');
                
                // LOGIKA 2: Kick User dari Active Connection (Sesuai permintaan Anda)
                $this->mikrotik->kickUser($username);
                
                $msg = "User $username berhasil dinonaktifkan dan ditendang (Kick) dari jaringan.";
            } else {
                // Enable Secret (Buka Blokir)
                $this->mikrotik->setSecretStatus($username, 'enabled');
                $msg = "User $username berhasil diaktifkan kembali.";
            }
            
            return back()->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah status user: ' . $e->getMessage());
        }
    }
}