<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->bigInteger('amount'); // jumlah topup (bisa positif/negatif)
            $table->string('description')->nullable(); // keterangan
            $table->timestamps();

            $table->index('customer_id');
            $table->index('admin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_balances');
    }
};
