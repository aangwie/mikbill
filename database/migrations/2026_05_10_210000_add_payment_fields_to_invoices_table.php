<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->bigInteger('paid_amount')->default(0)->after('price'); // jumlah yang dibayar
            $table->bigInteger('outstanding')->default(0)->after('paid_amount'); // kekurangan bayar
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'outstanding']);
        });
    }
};
