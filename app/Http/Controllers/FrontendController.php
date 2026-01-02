<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Company;
use App\Models\Plan;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Tambahkan ini

class FrontendController extends Controller
{
    // 1. Tampilkan Halaman Depan
    public function index()
    {
        return view('frontend.home');
    }

    // 1.1 Tampilkan Halaman Paket
    public function pricing()
    {
        $plans = Plan::all();
        return view('frontend.pricing', compact('plans'));
    }

    // 1.2 Tampilkan Halaman Tentang Kami
    public function about()
    {
        $setting = SiteSetting::first();
        return view('frontend.about', compact('setting'));
    }

    // 1.3 Tampilkan Halaman Syarat & Ketentuan
    public function terms()
    {
        $setting = SiteSetting::first();
        return view('frontend.terms', compact('setting'));
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
        $invoice = Invoice::with('customer')->findOrFail($id);
        $company = Company::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('admin_id', $invoice->admin_id)
            ->first();

        // Convert Logo to Base64 for PDF
        $logoBase64 = null;
        if ($company && !empty($company->logo_path)) {
            $path = public_path('uploads/' . $company->logo_path);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $data = [
            'invoice' => $invoice,
            'company' => $company,
            'logoBase64' => $logoBase64,
            'isPdf' => true
        ];

        $pdf = Pdf::loadView('billing.invoice', $data);

        $pdf->setOptions(['isRemoteEnabled' => true]);
        $pdf->setPaper('a4', 'portrait');

        $fileName = 'Invoice-' . $invoice->customer->internet_number . '-' . $invoice->id . '.pdf';

        return $pdf->download($fileName);
    }
}
