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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('name');
            $table->string('profile')->nullable()->after('pppoe_password'); // Simpan nama profile mikrotik
            $table->string('comment')->nullable()->after('profile');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['phone', 'profile', 'comment']);
        });
    }
};
