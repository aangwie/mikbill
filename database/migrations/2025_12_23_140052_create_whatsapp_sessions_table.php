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
        Schema::create('whatsapp_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id'); // Untuk membedakan session (default: 'primary')
            $table->string('key_id'); // Nama file/key credential
            $table->longText('value'); // Isi credential
            $table->timestamps();

            // Unique constraint agar tidak duplikat per session + key
            $table->unique(['session_id', 'key_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_sessions');
    }
};
