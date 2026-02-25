<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('scheduled_messages', function (Blueprint $table) {
            $table->string('broadcast_type', 20)->default('all')->after('whatsapp_age'); // 'all' or 'unpaid'
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_messages', function (Blueprint $table) {
            $table->dropColumn('broadcast_type');
        });
    }
};
