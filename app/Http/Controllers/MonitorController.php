<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MikrotikService;

class MonitorController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    public function dhcpLeases()
    {
        $leases = $this->mikrotik->getDhcpLeases();
        return view('monitor.dhcp_leases', compact('leases'));
    }

    public function staticUsers()
    {
        $users = $this->mikrotik->getHotspotUsers();
        return view('monitor.static_users', compact('users'));
    }

    public function simpleQueues()
    {
        $queues = $this->mikrotik->getSimpleQueues();
        return view('monitor.simple_queues', compact('queues'));
    }

    public function getSimpleQueuesJson()
    {
        $queues = $this->mikrotik->getSimpleQueues();
        return response()->json($queues);
    }
}
