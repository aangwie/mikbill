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
        Schema::table('payment_settings', function (Blueprint $table) {
            $table->string('active_provider')->default('xendit')->after('id');
            $table->string('midtrans_server_key')->nullable()->after('xendit_callback_token');
            $table->string('midtrans_client_key')->nullable()->after('midtrans_server_key');
            $table->boolean('midtrans_is_production')->default(false)->after('midtrans_client_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            //
        });
    }
};
