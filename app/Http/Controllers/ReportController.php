<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil User yang Login
        $user = Auth::user();

        // 2. Filter Bulan & Tahun
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        // 3. Mulai Query
        $query = Invoice::with('customer')
            ->whereMonth('due_date', $month)
            ->whereYear('due_date', $year);

        // --- LOGIKA PEMBATASAN DATA (Operator) ---
        if ($user->role == 'operator') {
            // Filter: Hanya invoice yang customer-nya dipegang operator ini
            $query->whereHas('customer', function($q) use ($user) {
                $q->where('operator_id', $user->id);
            });
        }
        // -----------------------------------------

        $invoices = $query->get();

        // 4. Hitung Rekapitulasi (Dari data yang sudah difilter di atas)
        $totalTagihan = $invoices->sum(fn($inv) => $inv->customer->monthly_price);
        
        $totalLunas = $invoices->where('status', 'paid')
                        ->sum(fn($inv) => $inv->customer->monthly_price);
        
        $totalBelumLunas = $invoices->where('status', 'unpaid')
                            ->sum(fn($inv) => $inv->customer->monthly_price);

        $jumlahLunas = $invoices->where('status', 'paid')->count();
        $jumlahBelumLunas = $invoices->where('status', 'unpaid')->count();

        return view('report.index', compact(
            'invoices', 
            'month', 
            'year', 
            'totalTagihan', 
            'totalLunas', 
            'totalBelumLunas',
            'jumlahLunas',
            'jumlahBelumLunas'
        ));
    }
}