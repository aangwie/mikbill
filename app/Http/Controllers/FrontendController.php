<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Company;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    // 1. Tampilkan Halaman Depan
    public function index()
    {
        return view('frontend.home');
    }

    // 2. Proses Cek Tagihan
    public function check(Request $request)
    {
        $request->validate([
            'internet_number' => 'required|string',
            'month' => 'required|numeric',
            'year' => 'required|numeric',
        ]);

        // Cari Customer berdasarkan Nomor Internet
        $customer = Customer::where('internet_number', $request->internet_number)->first();

        if (!$customer) {
            return back()->with('error', 'Nomor Internet tidak ditemukan.');
        }

        // Cari Tagihan pada Bulan & Tahun tersebut
        $invoice = Invoice::where('customer_id', $customer->id)
            ->whereMonth('due_date', $request->month)
            ->whereYear('due_date', $request->year)
            ->first();

        if (!$invoice) {
            return back()->with('error', 'Tidak ada tagihan ditemukan untuk periode tersebut.');
        }

        // Kembalikan ke halaman depan dengan membawa data invoice
        return view('frontend.home', compact('invoice', 'customer'));
    }

    // 3. Cetak Invoice (Versi Publik Tanpa Login)
    public function downloadInvoice($id)
    {
        // Ambil invoice
        $invoice = Invoice::with('customer')->findOrFail($id);
        
        // Ambil data perusahaan untuk kop surat
        $company = Company::first();

        // Gunakan view yang sama dengan admin, tapi lewat jalur publik
        return view('billing.invoice', compact('invoice', 'company'));
    }
}