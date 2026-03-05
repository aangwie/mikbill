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
        $mapData = $this->getMapData();
        return view('maps.index', compact('mapData'));
    }

    public function data()
    {
        return response()->json($this->getMapData());
    }

    private function getMapData()
    {
        $user = auth()->user();

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
                // Buat collection key-by username agar mudah dicek
                $onlineUsers = collect($actives)->pluck('name')->flip();
            }
        } catch (\Exception $e) {
            // Ignore error jika mikrotik mati
        }

        // 3. Format Data untuk Map
        return $customers->map(function ($c) use ($onlineUsers) {
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
    }
}