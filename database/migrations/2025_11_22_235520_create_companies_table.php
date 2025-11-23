<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('companies', function (Blueprint $table) {
        $table->id();
        $table->string('company_name');
        $table->text('address')->nullable();
        $table->string('phone')->nullable();
        $table->string('owner_name')->nullable();
        $table->string('logo_path')->nullable(); // Path gambar Logo
        $table->string('signature_path')->nullable(); // Path gambar Tanda Tangan
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
