<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat Customer (Pastikan username ini ADA di Mikrotik Anda untuk tes kick)
        $customer = Customer::create([
            'name' => 'z_depan',
            'pppoe_username' => 'z_depan', // Ganti dengan user pppoe asli di mikrotik Anda
            'pppoe_password' => 'rumah123',
            'monthly_price' => 150000,
            'is_active' => true,
        ]);

        // 2. Buat Tagihan "Nunggak" (Jatuh tempo bulan lalu)
        Invoice::create([
            'customer_id' => $customer->id,
            'due_date' => Carbon::now()->subDays(5), // Jatuh tempo 5 hari lalu
            'status' => 'unpaid',
        ]);
        
        $this->command->info('Data dummy berhasil dibuat!');
    }
}