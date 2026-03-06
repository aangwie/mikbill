<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileCustomer;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class MobileMapController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Ambil Pelanggan yang punya Koordinat saja (Mobile-specific scoping)
        $customers = MobileCustomer::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        // 2. Ambil Data User Online dari SEMUA Mikrotik yang aktif
        $onlineUsers = collect([]);
        try {
            $actives = $this->mikrotik->getAllActiveUsers();
            $onlineUsers = collect($actives)->pluck('name')->flip();
        } catch (\Exception $e) {
            // Error mikrotik, biarkan offline
        }

        // 3. Format Data untuk Mobile Map
        $mapData = $customers->map(function ($c) use ($onlineUsers) {
            $isOnline = $onlineUsers->has($c->pppoe_username);
            return [
                'id' => $c->id,
                'name' => $c->name,
                'username' => $c->pppoe_username,
                'lat' => (double) $c->latitude,
                'lng' => (double) $c->longitude,
                'status' => $isOnline ? 'online' : 'offline',
                'address' => $c->address ?? 'Tidak ada alamat',
                'phone' => $c->phone,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $mapData
        ]);
    }
}
