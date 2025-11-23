<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index()
    {
        // Ambil data pertama. Jika belum ada, buat objek kosong baru.
        $company = Company::first() ?? new Company();
        return view('company.index', compact('company'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            // Validasi baru
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'account_holder' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'signature' => 'nullable|image|max:2048',
        ]);

        $company = Company::first();
        if (!$company) { $company = new Company(); }

        $company->company_name = $request->company_name;
        $company->address = $request->address;
        $company->phone = $request->phone;
        $company->owner_name = $request->owner_name;

        // SIMPAN DATA BANK (BARU)
        $company->bank_name = $request->bank_name;
        $company->account_number = $request->account_number;
        $company->account_holder = $request->account_holder;

        // ... Logika upload gambar (biarkan seperti sebelumnya) ...
        if ($request->hasFile('logo')) {
            // ... (kode upload logo lama) ...
             if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $company->logo_path = $request->file('logo')->store('company_assets', 'public');
        }

        if ($request->hasFile('signature')) {
            // ... (kode upload ttd lama) ...
             if ($company->signature_path && Storage::disk('public')->exists($company->signature_path)) {
                Storage::disk('public')->delete($company->signature_path);
            }
            $company->signature_path = $request->file('signature')->store('company_assets', 'public');
        }

        $company->save();

        return back()->with('success', 'Data perusahaan & rekening berhasil disimpan.');
    }
}