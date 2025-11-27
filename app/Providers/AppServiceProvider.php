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
        // Cek dulu apakah tabel companies sudah ada (agar tidak error saat migrate fresh)
        if (Schema::hasTable('companies')) {
            $company = Company::first();
            
            // Jika ada logo di database, pakai itu. Jika tidak, pakai default laravel (favicon.ico)
            $faviconUrl = ($company && $company->logo_path) 
                ? asset('uploads/' . $company->logo_path)  // <-- Perhatikan 'uploads/'
                : asset('favicon.ico');

            // Bagikan variable $global_favicon ke semua view
            View::share('global_favicon', $faviconUrl);
        }
    }
}