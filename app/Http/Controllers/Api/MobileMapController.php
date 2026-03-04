<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
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

        // 1. Ambil Pelanggan yang punya Koordinat saja, filtered by user role
        $query = Customer::whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($user->role === 'operator') {
            $query->where('operator_id', $user->id);
        } elseif ($user->role === 'admin' || $user->role === 'superadmin') {
            $query->where('admin_id', $user->id);
        }

        $customers = $query->get();

        // 2. Ambil Data User Online dari Mikrotik
        $onlineUsers = collect([]);
        try {
            if ($this->mikrotik->isConnected()) {
                $actives = $this->mikrotik->getActiveUsers();
                $onlineUsers = collect($actives)->pluck('name')->flip();
            }
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
