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
        if (!Schema::hasColumn('site_settings', 'github_token')) {
            Schema::table('site_settings', function (Blueprint $table) {
                $table->string('github_token')->nullable()->after('terms_conditions');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('site_settings', 'github_token')) {
            Schema::table('site_settings', function (Blueprint $table) {
                $table->dropColumn('github_token');
            });
        }
    }
};
