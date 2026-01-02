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
            $table->text('adsense_content')->nullable()->after('sender_number');
            $table->string('adsense_url')->nullable()->after('adsense_content');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_settings', function (Blueprint $table) {
            $table->dropColumn(['adsense_content', 'adsense_url']);
        });
    }
};
