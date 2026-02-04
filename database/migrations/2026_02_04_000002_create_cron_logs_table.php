<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cron_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('command');
            $blueprint->string('status'); // success, failed
            $blueprint->longText('output')->nullable();
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cron_logs');
    }
};
