<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('users')->get();
        return view('superadmin.plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'max_routers' => 'required|integer|min:0',
            'max_customers' => 'required|integer|min:0',
            'max_vouchers' => 'required|integer|min:0',
            'price_monthly' => 'required|numeric|min:0',
            'price_semester' => 'required|numeric|min:0',
            'price_annual' => 'required|numeric|min:0',
            'stock_limit' => 'nullable|integer|min:0'
        ]);

        Plan::create($request->all() + [
            'wa_gateway' => $request->has('wa_gateway'),
            'customer_support' => $request->has('customer_support'),
            'is_active' => $request->has('is_active')
        ]);

        return back()->with('success', 'Paket berhasil ditambahkan.');
    }

    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'max_routers' => 'required|integer|min:0',
            'max_customers' => 'required|integer|min:0',
            'max_vouchers' => 'required|integer|min:0',
            'price_monthly' => 'required|numeric|min:0',
            'price_semester' => 'required|numeric|min:0',
            'price_annual' => 'required|numeric|min:0',
            'stock_limit' => 'nullable|integer|min:0'
        ]);

        $plan->update($request->all() + [
            'wa_gateway' => $request->has('wa_gateway'),
            'customer_support' => $request->has('customer_support'),
            'is_active' => $request->has('is_active')
        ]);

        return back()->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->users()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus paket yang masih digunakan oleh pelanggan.');
        }

        $plan->delete();
        return back()->with('success', 'Paket berhasil dihapus.');
    }

    public function publicIndex()
    {
        $plans = Plan::withCount('users')->get();
        $company = \App\Models\Company::first();
        return view('router.plans', compact('plans', 'company'));
    }
}
