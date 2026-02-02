<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // LOGIKA FAVICON GLOBAL
        // Cek dulu apakah tabel companies sudah ada dan aplikasi tidak sedang berjalan di console (migrate, dsb)
        // Agar tidak error saat migrate fresh atau saat kolom belum ada
        if (!app()->runningInConsole() && Schema::hasTable('companies')) {
            $company = null;

            // Cek apakah kolom admin_id sudah ada (karena ditambahkan lewat migrasi)
            if (Schema::hasColumn('companies', 'admin_id')) {
                // Kita ambil company milik user yang punya role superadmin
                $company = Company::whereHas('admin', function ($q) {
                    $q->where('role', 'superadmin');
                })->first();
            }

            // Jika tidak ada (mungkin belum set atau kolom belum ada), ambil yang pertama saja
            if (!$company) {
                $company = Company::first();
            }

            // Jika ada logo di database, pakai itu. Jika tidak, pakai default laravel (favicon.ico)
            $faviconUrl = ($company && $company->logo_path)
                ? asset('uploads/' . $company->logo_path)
                : asset('favicon.ico');

            // Bagikan variable $global_favicon dan $company ke semua view
            View::share('global_favicon', $faviconUrl);
            View::share('company', $company);
        }
    }
}