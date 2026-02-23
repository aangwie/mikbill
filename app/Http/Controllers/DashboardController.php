<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Services\MikrotikService;

class DashboardController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    public function index()
    {
        $user = auth()->user();

        // ── Base Queries with Role Filtering ──
        $customerQuery = Customer::query();
        $invoiceQuery = Invoice::query();

        if ($user->role === 'operator') {
            $customerQuery->where('operator_id', $user->id);
            $invoiceQuery->whereHas('customer', function ($q) use ($user) {
                $q->where('operator_id', $user->id);
            });
        } elseif ($user->role === 'admin' || $user->role === 'superadmin') {
            $customerQuery->where('admin_id', $user->id);
            $invoiceQuery->where('admin_id', $user->id);
        }

        // ── Customer Stats (Optimized: Fetch from Mikrotik for real-time status) ──
        $isConnected = $this->mikrotik->isConnected();
        $totalCustomers = 0;
        $activeCustomers = 0;
        $disabledCustomers = 0;

        // Get list of usernames belonging to this user from local DB for filtering Mikrotik results
        $myCustomerUsernames = (clone $customerQuery)->pluck('pppoe_username')->filter()->toArray();

        if ($isConnected) {
            $secrets = $this->mikrotik->getSecrets();
            $actives = $this->mikrotik->getActiveUsers();

            // Filter Mikrotik data to only show what belongs to this login ID
            $mySecrets = array_filter($secrets, function ($s) use ($myCustomerUsernames) {
                return in_array($s['name'], $myCustomerUsernames);
            });
            $myActives = array_filter($actives, function ($a) use ($myCustomerUsernames) {
                return in_array($a['name'], $myCustomerUsernames);
            });

            $totalCustomers = count($mySecrets);
            $activeCustomers = count($myActives);
            $disabledCustomers = $totalCustomers - $activeCustomers;
        } else {
            // Fallback to local DB if Mikrotik is disconnected
            $totalCustomers = (clone $customerQuery)->count();
            $activeCustomers = (clone $customerQuery)->where('is_active', true)->count();
            $disabledCustomers = (clone $customerQuery)->where('is_active', false)->count();
        }

        // ── Billing Stats (current month) ──
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        $invoicesThisMonth = (clone $invoiceQuery)
            ->whereMonth('due_date', $month)
            ->whereYear('due_date', $year)
            ->get();

        $totalBilling = 0;
        $paidBilling = 0;
        $unpaidBilling = 0;

        foreach ($invoicesThisMonth as $inv) {
            $price = $inv->price > 0 ? $inv->price : ($inv->customer->monthly_price ?? 0);
            $totalBilling += $price;
            if ($inv->status === 'paid') {
                $paidBilling += $price;
            } else {
                $unpaidBilling += $price;
            }
        }

        // ── Payment Chart Data (last 6 months) ──
        $chartLabels = [];
        $chartPaid = [];
        $chartUnpaid = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $chartLabels[] = $date->locale('id')->isoFormat('MMM YYYY');

            $monthInvoices = (clone $invoiceQuery)
                ->whereMonth('due_date', $date->month)
                ->whereYear('due_date', $date->year)
                ->get();

            $paid = 0;
            $unpaid = 0;
            foreach ($monthInvoices as $inv) {
                $price = $inv->price > 0 ? $inv->price : ($inv->customer->monthly_price ?? 0);
                if ($inv->status === 'paid') {
                    $paid += $price;
                } else {
                    $unpaid += $price;
                }
            }
            $chartPaid[] = $paid;
            $chartUnpaid[] = $unpaid;
        }

        // ── System Information ──
        $phpVersion = phpversion();
        $laravelVersion = app()->version();

        try {
            $dbVersionRaw = DB::select('SELECT VERSION() as ver');
            $dbVersion = $dbVersionRaw[0]->ver ?? 'Unknown';
        } catch (\Exception $e) {
            $dbVersion = 'Unknown';
        }

        $libraries = [
            ['name' => 'zlib (gz)', 'loaded' => extension_loaded('zlib')],
            ['name' => 'zip', 'loaded' => extension_loaded('zip')],
            ['name' => 'mbstring', 'loaded' => extension_loaded('mbstring')],
            ['name' => 'curl', 'loaded' => extension_loaded('curl')],
            ['name' => 'openssl', 'loaded' => extension_loaded('openssl')],
            ['name' => 'gd', 'loaded' => extension_loaded('gd')],
            ['name' => 'fileinfo', 'loaded' => extension_loaded('fileinfo')],
            ['name' => 'pdo_mysql', 'loaded' => extension_loaded('pdo_mysql')],
            ['name' => 'json', 'loaded' => extension_loaded('json')],
            ['name' => 'tokenizer', 'loaded' => extension_loaded('tokenizer')],
        ];

        return view('dashboard.index', compact(
            'totalCustomers',
            'activeCustomers',
            'disabledCustomers',
            'totalBilling',
            'paidBilling',
            'unpaidBilling',
            'chartLabels',
            'chartPaid',
            'chartUnpaid',
            'isConnected',
            'phpVersion',
            'laravelVersion',
            'dbVersion',
            'libraries'
        ));
    }
}
