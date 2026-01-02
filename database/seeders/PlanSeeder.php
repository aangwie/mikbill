<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Plan::updateOrCreate(['name' => 'Starter'], [
            'max_routers' => 1,
            'max_customers' => 100,
            'wa_gateway' => false,
            'price_monthly' => 50000,
            'price_semester' => 250000,
            'price_annual' => 450000,
            'description' => 'Paket hemat untuk usaha mandiri.'
        ]);

        \App\Models\Plan::updateOrCreate(['name' => 'Business'], [
            'max_routers' => 3,
            'max_customers' => 500,
            'wa_gateway' => true,
            'price_monthly' => 150000,
            'price_semester' => 750000,
            'price_annual' => 1350000,
            'description' => 'Paket profesional untuk berkembang.'
        ]);

        \App\Models\Plan::updateOrCreate(['name' => 'Premium'], [
            'max_routers' => 10,
            'max_customers' => 5000,
            'wa_gateway' => true,
            'price_monthly' => 350000,
            'price_semester' => 1750000,
            'price_annual' => 3150000,
            'description' => 'Paket lengkap tanpa batas.'
        ]);

        // Berikan paket Starter ke semua User yang belum punya paket
        $starter = \App\Models\Plan::where('name', 'Starter')->first();
        \App\Models\User::whereNull('plan_id')->update(['plan_id' => $starter->id]);
    }
}
