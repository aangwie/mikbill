<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Modify Role Enum in Users Table
        // We use raw SQL because we are adding a value to an ENUM
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'admin', 'operator') NOT NULL DEFAULT 'operator'");

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('role')->constrained('users')->onDelete('set null');
            }
        });

        // 2. Add admin_id to Resource Tables
        $tables = ['customers', 'companies', 'router_settings'];
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'admin_id')) {
                    $table->foreignId('admin_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        // Revert Role Enum
        // Warning: This will fail if there are 'superadmin' users.
        // For now we assume rollback is done before data population or handled manually.
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'operator') NOT NULL DEFAULT 'operator'");

        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key first using array syntax to guess index name
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });

        $tables = ['customers', 'companies', 'router_settings'];
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['admin_id']);
                $table->dropColumn('admin_id');
            });
        }
    }
};
