<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class MapController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    public function index()
    {
        // 1. Ambil Pelanggan yang punya Koordinat saja
        $customers = Customer::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        // 2. Ambil Data User Online dari Mikrotik
        $onlineUsers = collect([]);
        try {
            if ($this->mikrotik->isConnected()) {
                $actives = $this->mikrotik->getActiveUsers();
                // Buat collection key-by username agar mudah dicek
                $onlineUsers = collect($actives)->pluck('name')->flip();
            }
        } catch (\Exception $e) {
            // Ignore error jika mikrotik mati, anggap semua offline
        }

        // 3. Format Data untuk Map (GeoJSON like)
        $mapData = $customers->map(function($c) use ($onlineUsers) {
            // Cek apakah user ini ada di daftar online mikrotik?
            $isOnline = $onlineUsers->has($c->pppoe_username);
            
            return [
                'name' => $c->name,
                'username' => $c->pppoe_username,
                'lat' => $c->latitude,
                'lng' => $c->longitude,
                'status' => $isOnline ? 'online' : 'offline',
                'address' => $c->address ?? 'Tidak ada alamat',
                'phone' => $c->phone,
            ];
        });

        return view('maps.index', compact('mapData'));
    }
}