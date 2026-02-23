<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
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
        } elseif ($user->role === 'admin') {
            $customerQuery->where('admin_id', $user->id);
            $invoiceQuery->where('admin_id', $user->id);
        }
        // Superadmin skips these filters to see everything.

        // ── Customer Stats ──
        $totalCustomers = (clone $customerQuery)->count();
        $activeCustomers = (clone $customerQuery)->where('is_active', true)->count();
        $disabledCustomers = (clone $customerQuery)->where('is_active', false)->count();

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
            'phpVersion',
            'laravelVersion',
            'dbVersion',
            'libraries'
        ));
    }
}
