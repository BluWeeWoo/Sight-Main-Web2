<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eye_health_metrics', function (Blueprint $table) {
            $table->integer('health_score')->nullable()->after('screen_time_minutes');
            $table->integer('coins')->nullable()->after('health_score');
        });
    }

    public function down(): void
    {
        Schema::table('eye_health_metrics', function (Blueprint $table) {
            $table->dropColumn(['health_score', 'coins']);
        });
    }
};