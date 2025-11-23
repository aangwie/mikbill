<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('phone');      // Contoh: BCA, BRI, Mandiri
            $table->string('account_number')->nullable()->after('bank_name'); // Contoh: 1234567890
            $table->string('account_holder')->nullable()->after('account_number'); // Contoh: PT. NetWiz
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'account_number', 'account_holder']);
        });
    }
};
