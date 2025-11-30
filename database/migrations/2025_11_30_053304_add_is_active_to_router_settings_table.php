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
    Schema::table('router_settings', function (Blueprint $table) {
        // Kolom penanda status aktif (Default false)
        $table->boolean('is_active')->default(false)->after('port');
        
        // Opsional: Kolom nama label (misal: "Router Utama")
        $table->string('label')->nullable()->after('id'); 
    });
}

public function down()
{
    Schema::table('router_settings', function (Blueprint $table) {
        $table->dropColumn(['is_active', 'label']);
    });
}
};
