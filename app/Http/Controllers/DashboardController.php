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

        try {
            $dbVersionRaw = DB::select('SELECT VERSION() as ver');
            $dbVersion = $dbVersionRaw[0]->ver ?? 'Unknown';
        } catch (\Exception $e) {
            $dbVersion = 'Unknown';
        }

        // ── System Monitor (Realtime) ──
        $systemStats = $this->getSystemMonitorStats();

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
            'dbVersion',
            'systemStats'
        ));
    }

    private function getSystemMonitorStats()
    {
        $stats = [
            'cpu_load' => 0,
            'ram_total' => 0,
            'ram_used' => 0,
            'ram_percentage' => 0,
            'disk_total' => 0,
            'disk_used' => 0,
            'disk_percentage' => 0,
            'net_rx' => 0,
            'net_tx' => 0,
        ];

        // Windows Specific Stats
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            try {
                // CPU Load
                // ... (existing CPU logic) ...
                $cpu = shell_exec('wmic cpu get loadpercentage');
                if ($cpu) {
                    $lines = explode("\n", trim($cpu));
                    if (isset($lines[1])) {
                        $stats['cpu_load'] = (int) trim($lines[1]);
                    }
                }

                // RAM Status
                // ... (existing RAM logic) ...
                $ram = shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value');
                if ($ram) {
                    $lines = explode("\n", trim($ram));
                    $ramData = [];
                    foreach ($lines as $line) {
                        if (strpos($line, '=') !== false) {
                            list($key, $value) = explode('=', $line);
                            $ramData[trim($key)] = (int) trim($value);
                        }
                    }
                    if (isset($ramData['TotalVisibleMemorySize']) && isset($ramData['FreePhysicalMemory'])) {
                        $stats['ram_total'] = $ramData['TotalVisibleMemorySize'] * 1024; // Convert KB to Bytes
                        $freeRam = $ramData['FreePhysicalMemory'] * 1024;
                        $stats['ram_used'] = $stats['ram_total'] - $freeRam;
                        $stats['ram_percentage'] = round(($stats['ram_used'] / $stats['ram_total']) * 100, 1);
                    }
                }

                // Network Status (Windows)
                $net = shell_exec('netstat -e');
                if ($net) {
                    $lines = explode("\n", trim($net));
                    foreach ($lines as $line) {
                        if (stripos($line, 'Bytes') !== false) {
                            $parts = preg_split('/\s+/', trim($line));
                            if (count($parts) >= 3) {
                                $stats['net_rx'] = (float) $parts[1];
                                $stats['net_tx'] = (float) $parts[2];
                            }
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Handle or log error
            }
        } else {
            // Linux / Shared Hosting Stats
            try {
                // CPU Load (using sys_getloadavg)
                if (function_exists('sys_getloadavg')) {
                    $load = sys_getloadavg();
                    if (isset($load[0])) {
                        $stats['cpu_load'] = min(round($load[0] * 100), 100);
                    }
                }

                // RAM Status (parsing /proc/meminfo)
                if (is_readable('/proc/meminfo')) {
                    $meminfo = file_get_contents('/proc/meminfo');
                    $data = [];
                    foreach (explode("\n", $meminfo) as $line) {
                        if (strpos($line, ':') !== false) {
                            list($key, $val) = explode(':', $line);
                            $data[trim($key)] = (int) trim($val) * 1024; // KB to Bytes
                        }
                    }

                    if (isset($data['MemTotal']) && isset($data['MemAvailable'])) {
                        $stats['ram_total'] = $data['MemTotal'];
                        $stats['ram_used'] = $data['MemTotal'] - $data['MemAvailable'];
                        $stats['ram_percentage'] = round(($stats['ram_used'] / $stats['ram_total']) * 100, 1);
                    } elseif (isset($data['MemTotal']) && isset($data['MemFree'])) {
                        $stats['ram_total'] = $data['MemTotal'];
                        $stats['ram_used'] = $data['MemTotal'] - $data['MemFree'];
                        $stats['ram_percentage'] = round(($stats['ram_used'] / $stats['ram_total']) * 100, 1);
                    }
                }

                // Network Status (Linux /proc/net/dev)
                if (is_readable('/proc/net/dev')) {
                    $netinfo = file_get_contents('/proc/net/dev');
                    $lines = explode("\n", $netinfo);
                    foreach ($lines as $line) {
                        if (strpos($line, ':') !== false) {
                            $parts = preg_split('/\s+/', trim($line));
                            $interface = trim(str_replace(':', '', $parts[0]));
                            if ($interface !== 'lo') {
                                $stats['net_rx'] += (float) $parts[1];
                                $stats['net_tx'] += (float) $parts[9];
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Fallback
            }
        }

        // Disk Usage (Cross-platform)
        $diskPath = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? "C:" : base_path();

        try {
            $ds = @disk_total_space($diskPath);
            $df = @disk_free_space($diskPath);

            if ($ds && $ds > 0) {
                $stats['disk_total'] = $ds;
                $stats['disk_used'] = $ds - $df;
                $stats['disk_percentage'] = round(($stats['disk_used'] / $stats['disk_total']) * 100, 1);
            }
        } catch (\Exception $e) {
            // Fallback if disk space cannot be determined
        }

        return $stats;
    }

    public function getStatsJson()
    {
        $stats = $this->getSystemMonitorStats();
        // Add human readable formats for JS
        $stats['ram_used_gb'] = round($stats['ram_used'] / (1024 ** 3), 2);
        $stats['ram_total_gb'] = round($stats['ram_total'] / (1024 ** 3), 2);
        $stats['disk_used_gb'] = round($stats['disk_used'] / (1024 ** 3), 2);
        $stats['disk_total_gb'] = round($stats['disk_total'] / (1024 ** 3), 2);

        return response()->json($stats);
    }
}
