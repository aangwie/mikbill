<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Services\MikrotikService;
use Carbon\Carbon;

class CheckUnpaidUsers extends Command
{
    // Nama perintah yang nanti diketik di terminal
    protected $signature = 'billing:check-unpaid';
    protected $description = 'Cek user telat bayar dan putus koneksi mikrotik';

    public function handle(MikrotikService $mikrotik)
    {
        $today = Carbon::now()->toDateString();

        $this->info("Memulai pengecekan tagihan jatuh tempo per: $today");

        // 1. Cari tagihan yang STATUS 'unpaid' DAN DUE_DATE < Hari Ini
        // Cari tagihan yang STATUS 'unpaid' DAN DUE_DATE < Hari Ini
        $overdueInvoices = Invoice::where('status', 'unpaid')
            ->where('due_date', '<', Carbon::now()->toDateString()) // Pastikan format tanggal benar
            ->with('customer')
            ->get();

        if ($overdueInvoices->count() == 0) {
            $this->info("Tidak ada user yang menunggak.");
            return;
        }

        // 2. Loop user yang nunggak
        foreach ($overdueInvoices as $invoice) {
            $user = $invoice->customer->pppoe_username;

            $this->info("Memproses isolir user: $user");

            try {
                // A. Disable Secret di Mikrotik (Ceklis Merah/X)
                $mikrotik->setSecretStatus($user, 'disabled');

                // B. Kick User (Supaya putus dari sesi aktif sekarang)
                $mikrotik->kickUser($user);

                // C. Update status customer di database lokal (Opsional)
                $invoice->customer->update(['is_active' => false]);

                $this->info("Berhasil memutus user: $user");
            } catch (\Exception $e) {
                $this->error("Gagal memutus $user: " . $e->getMessage());
            }
        }

        $this->info("Selesai.");
    }
}
