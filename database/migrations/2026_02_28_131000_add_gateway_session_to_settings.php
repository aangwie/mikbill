<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('whatsapp_settings', function (Blueprint $table) {
            $table->string('gateway_session')->nullable()->unique()->after('api_key_gateway');
        });

        // Populate existing records with a unique session ID
        DB::table('whatsapp_settings')->get()->each(function ($setting) {
            DB::table('whatsapp_settings')->where('id', $setting->id)->update([
                'gateway_session' => 'sess_' . Str::random(12)
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_settings', function (Blueprint $table) {
            $table->dropColumn('gateway_session');
        });
    }
};
