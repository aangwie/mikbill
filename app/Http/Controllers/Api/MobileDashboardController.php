<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MobileDashboardController extends Controller
{
    /**
     * GET /api/mobile/dashboard
     * Return dashboard stats for authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Customer stats
        $customerQuery = Customer::query();
        $invoiceQuery = Invoice::query();

        if ($user->role === 'operator') {
            $customerQuery->where('operator_id', $user->id);
            $invoiceQuery->whereHas('customer', fn($q) => $q->where('operator_id', $user->id));
        } elseif ($user->role === 'admin') {
            $customerQuery->where('admin_id', $user->id);
            $invoiceQuery->whereHas('customer', fn($q) => $q->where('admin_id', $user->id));
        }

        $totalCustomers = (clone $customerQuery)->count();
        $activeCustomers = (clone $customerQuery)->where('is_active', true)->count();
        $disabledCustomers = (clone $customerQuery)->where('is_active', false)->count();

        // Billing stats (current month)
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        $invoicesThisMonth = (clone $invoiceQuery)
            ->with('customer')
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

        // Chart data (last 6 months)
        $chartLabels = [];
        $chartPaid = [];
        $chartUnpaid = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $chartLabels[] = $date->locale('id')->isoFormat('MMM YYYY');

            $monthInvoices = (clone $invoiceQuery)
                ->with('customer')
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

        // Recent unpaid invoices
        $recentUnpaid = (clone $invoiceQuery)
            ->with('customer:id,name,internet_number,phone')
            ->where('status', 'unpaid')
            ->orderBy('due_date', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($inv) => [
                'id' => $inv->id,
                'customer_name' => $inv->customer->name ?? '-',
                'internet_number' => $inv->customer->internet_number ?? '-',
                'due_date' => $inv->due_date,
                'price' => $inv->price > 0 ? $inv->price : ($inv->customer->monthly_price ?? 0),
                'status' => $inv->status,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'customers' => [
                    'total' => $totalCustomers,
                    'active' => $activeCustomers,
                    'disabled' => $disabledCustomers,
                ],
                'billing' => [
                    'total' => $totalBilling,
                    'paid' => $paidBilling,
                    'unpaid' => $unpaidBilling,
                    'month' => $month,
                    'year' => $year,
                ],
                'chart' => [
                    'labels' => $chartLabels,
                    'paid' => $chartPaid,
                    'unpaid' => $chartUnpaid,
                ],
                'recent_unpaid' => $recentUnpaid,
            ],
        ]);
    }
}
