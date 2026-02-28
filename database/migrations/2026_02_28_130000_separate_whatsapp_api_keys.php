<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('whatsapp_settings', function (Blueprint $table) {
            $table->string('api_key_external')->nullable()->after('api_key');
            $table->string('api_key_gateway')->nullable()->after('api_key_external');
        });

        // Migrate existing data if possible
        DB::table('whatsapp_settings')->get()->each(function ($setting) {
            if ($setting->wa_provider === 'api') {
                DB::table('whatsapp_settings')->where('id', $setting->id)->update(['api_key_external' => $setting->api_key]);
            } elseif ($setting->wa_provider === 'gateway') {
                DB::table('whatsapp_settings')->where('id', $setting->id)->update(['api_key_gateway' => $setting->api_key]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_settings', function (Blueprint $table) {
            $table->dropColumn(['api_key_external', 'api_key_gateway']);
        });
    }
};
