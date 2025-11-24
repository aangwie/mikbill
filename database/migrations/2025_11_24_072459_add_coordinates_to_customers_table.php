<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // LANGKAH 1: Pastikan kolom address dibuat DULUAN
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'address')) {
                $table->text('address')->nullable()->after('name');
            }
        });

        // LANGKAH 2: Baru buat kolom latitude & longitude setelahnya
        Schema::table('customers', function (Blueprint $table) {
            // Cek agar tidak error jika dijalankan ulang
            if (!Schema::hasColumn('customers', 'latitude')) {
                $table->string('latitude')->nullable()->after('address');
            }
            if (!Schema::hasColumn('customers', 'longitude')) {
                $table->string('longitude')->nullable()->after('latitude');
            }
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'address']);
        });
    }
};