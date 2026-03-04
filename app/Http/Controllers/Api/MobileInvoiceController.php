<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MobileInvoiceController extends Controller
{
    /**
     * GET /api/mobile/invoices
     * List invoices filtered by month/year.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        $query = Invoice::with('customer:id,name,internet_number,phone,monthly_price')
            ->whereMonth('due_date', $month)
            ->whereYear('due_date', $year)
            ->orderByRaw("FIELD(status, 'unpaid', 'paid')")
            ->orderBy('due_date', 'asc');

        if ($user->role === 'operator') {
            $query->whereHas('customer', fn($q) => $q->where('operator_id', $user->id));
        }

        // Search
        if ($search = $request->input('search')) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('internet_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->paginate($request->input('per_page', 20));

        // Calculate totals
        $totalsQuery = Invoice::whereMonth('due_date', $month)
            ->whereYear('due_date', $year);

        if ($user->role === 'operator') {
            $totalsQuery->whereHas('customer', fn($q) => $q->where('operator_id', $user->id));
        }

        $allInvoices = $totalsQuery->with('customer')->get();

        $totalBilling = 0;
        $paidBilling = 0;
        $unpaidBilling = 0;

        foreach ($allInvoices as $inv) {
            $price = $inv->price > 0 ? $inv->price : ($inv->customer->monthly_price ?? 0);
            $totalBilling += $price;
            if ($inv->status === 'paid') {
                $paidBilling += $price;
            } else {
                $unpaidBilling += $price;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $invoices,
            'summary' => [
                'total' => $totalBilling,
                'paid' => $paidBilling,
                'unpaid' => $unpaidBilling,
            ],
        ]);
    }

    /**
     * POST /api/mobile/invoices/{id}/pay
     * Mark invoice as paid.
     */
    public function pay(Request $request, $id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);
        $customer = $invoice->customer;
        $user = $request->user();

        // Permission check
        if ($user->role === 'operator' && $customer->operator_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice sudah lunas.',
            ]);
        }

        $invoice->update(['status' => 'paid']);
        $customer->update(['is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dicatat.',
            'data' => $invoice->fresh(),
        ]);
    }

    /**
     * POST /api/mobile/invoices/{id}/cancel
     * Cancel payment (revert to unpaid).
     */
    public function cancel(Request $request, $id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);
        $customer = $invoice->customer;
        $user = $request->user();

        // Permission check
        if ($user->role === 'operator' && $customer->operator_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if ($invoice->status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice belum lunas.',
            ]);
        }

        $invoice->update(['status' => 'unpaid']);
        $customer->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran dibatalkan.',
            'data' => $invoice->fresh(),
        ]);
    }

    /**
     * GET /api/mobile/check-bill (Public)
     * Check bill by internet number, month, year.
     */
    public function checkBill(Request $request)
    {
        $request->validate([
            'internet_number' => 'required|string',
            'month' => 'required|numeric',
            'year' => 'required|numeric',
        ]);

        $customer = Customer::where('internet_number', $request->internet_number)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor Internet tidak ditemukan.',
            ], 404);
        }

        $invoice = Invoice::where('customer_id', $customer->id)
            ->whereMonth('due_date', $request->month)
            ->whereYear('due_date', $request->year)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada tagihan untuk periode tersebut.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => [
                    'name' => $customer->name,
                    'internet_number' => $customer->internet_number,
                    'address' => $customer->address,
                ],
                'invoice' => [
                    'id' => $invoice->id,
                    'due_date' => $invoice->due_date,
                    'price' => $invoice->price > 0 ? $invoice->price : ($customer->monthly_price ?? 0),
                    'status' => $invoice->status,
                ],
            ],
        ]);
    }
}
