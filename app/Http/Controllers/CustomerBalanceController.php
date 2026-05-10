<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerBalance;
use Illuminate\Http\Request;

class CustomerBalanceController extends Controller
{
    /**
     * Simpan topup saldo (AJAX)
     */
    public function store(Request $request, $customerId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $customer = Customer::findOrFail($customerId);

        $balance = CustomerBalance::create([
            'customer_id' => $customer->id,
            'amount' => $request->amount,
            'description' => $request->description ?? 'Topup Saldo',
        ]);

        $totalBalance = CustomerBalance::where('customer_id', $customer->id)->sum('amount');

        return response()->json([
            'success' => true,
            'message' => 'Saldo berhasil ditambahkan!',
            'balance' => $balance,
            'total_balance' => $totalBalance,
        ]);
    }

    /**
     * Halaman riwayat saldo pelanggan
     */
    public function history($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $balances = CustomerBalance::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();
        $totalBalance = $balances->sum('amount');

        return view('customers.balance_history', compact('customer', 'balances', 'totalBalance'));
    }

    /**
     * Update entri saldo (AJAX)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $balance = CustomerBalance::findOrFail($id);
        $balance->update([
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        $totalBalance = CustomerBalance::where('customer_id', $balance->customer_id)->sum('amount');

        return response()->json([
            'success' => true,
            'message' => 'Saldo berhasil diperbarui!',
            'balance' => $balance,
            'total_balance' => $totalBalance,
        ]);
    }

    /**
     * Hapus entri saldo (AJAX)
     */
    public function destroy($id)
    {
        $balance = CustomerBalance::findOrFail($id);
        $customerId = $balance->customer_id;
        $balance->delete();

        $totalBalance = CustomerBalance::where('customer_id', $customerId)->sum('amount');

        return response()->json([
            'success' => true,
            'message' => 'Entri saldo berhasil dihapus!',
            'total_balance' => $totalBalance,
        ]);
    }
}
