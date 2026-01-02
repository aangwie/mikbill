<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('max_routers')->default(1);
            $table->integer('max_customers')->default(100);
            $table->boolean('wa_gateway')->default(false);
            $table->decimal('price_monthly', 12, 2)->default(0);
            $table->decimal('price_semester', 12, 2)->default(0);
            $table->decimal('price_annual', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
