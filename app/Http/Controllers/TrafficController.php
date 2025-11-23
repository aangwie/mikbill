<?php

namespace App\Http\Controllers;

use App\Services\MikrotikService;
use Illuminate\Http\Request;

class TrafficController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    // 1. Tampilkan Halaman Traffic
    public function index()
    {
        $interfaces = [];
        try {
            if ($this->mikrotik->isConnected()) {
                $interfaces = $this->mikrotik->getInterfaces();
            }
        } catch (\Exception $e) {
            // Handle error silent
        }

        return view('traffic.index', compact('interfaces'));
    }

    // 2. API Endpoint untuk AJAX (Data Traffic Live)
    public function data(Request $request)
    {
        $interface = $request->input('interface');
        
        try {
            $data = $this->mikrotik->getTraffic($interface);
            return response()->json([
                'status' => 'success',
                'rx' => (int) $data['rx'],
                'tx' => (int) $data['tx']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'rx' => 0,
                'tx' => 0
            ]);
        }
    }
}