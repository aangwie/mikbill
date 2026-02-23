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
        Schema::table('whatsapp_settings', function (Blueprint $table) {
            $table->string('wa_provider')->default('api')->after('sender_number'); // api or gateway
            $table->string('wa_gateway_url')->nullable()->after('wa_provider');
            $table->string('wa_gateway_status')->default('disconnected')->after('wa_gateway_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_settings', function (Blueprint $table) {
            $table->dropColumn(['wa_provider', 'wa_gateway_url', 'wa_gateway_status']);
        });
    }
};
