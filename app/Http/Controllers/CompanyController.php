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
        if (!$company) {
            $company = new Company();
        }

        $company->company_name = $request->company_name;
        $company->address = $request->address;
        $company->phone = $request->phone;
        $company->owner_name = $request->owner_name;

        // Simpan Data Bank
        $company->bank_name = $request->bank_name;
        $company->account_number = $request->account_number;
        $company->account_holder = $request->account_holder;

        // --- LOGIKA UPLOAD BARU (Disk: hosting) ---

        // 1. UPLOAD LOGO
        if ($request->hasFile('logo')) {
            // Hapus file lama jika ada (Cek di disk hosting)
            if ($company->logo_path && \Illuminate\Support\Facades\Storage::disk('hosting')->exists($company->logo_path)) {
                \Illuminate\Support\Facades\Storage::disk('hosting')->delete($company->logo_path);
            }

            // Simpan ke folder 'company_assets' di dalam 'public/uploads'
            $path = $request->file('logo')->store('company_assets', 'hosting');
            $company->logo_path = $path;
        }

        // 2. UPLOAD TANDA TANGAN
        if ($request->hasFile('signature')) {
            // Hapus file lama
            if ($company->signature_path && \Illuminate\Support\Facades\Storage::disk('hosting')->exists($company->signature_path)) {
                \Illuminate\Support\Facades\Storage::disk('hosting')->delete($company->signature_path);
            }

            // Simpan
            $path = $request->file('signature')->store('company_assets', 'hosting');
            $company->signature_path = $path;
        }

        $company->save();

        return back()->with('success', 'Data perusahaan berhasil disimpan (Direct Upload).');
    }
}
