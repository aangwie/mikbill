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
    Schema::create('whatsapp_settings', function (Blueprint $table) {
        $table->id();
        $table->string('target_url'); // URL API Endpoint
        $table->string('api_key');
        $table->string('sender_number')->nullable(); // Nomor pengirim (opsional tergantung provider)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_settings');
    }
};
