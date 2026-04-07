<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('doctor_profile')) {
            return;
        }

        Schema::table('doctor_profile', function (Blueprint $table) {
            if (!Schema::hasColumn('doctor_profile', 'phone')) {
                $table->string('phone')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('doctor_profile', 'specialty')) {
                $table->string('specialty')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('doctor_profile', 'clinic')) {
                $table->string('clinic')->nullable()->after('specialty');
            }
            if (!Schema::hasColumn('doctor_profile', 'location')) {
                $table->string('location')->nullable()->after('clinic');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('doctor_profile')) {
            return;
        }

        Schema::table('doctor_profile', function (Blueprint $table) {
            if (Schema::hasColumn('doctor_profile', 'location')) {
                $table->dropColumn('location');
            }
            if (Schema::hasColumn('doctor_profile', 'clinic')) {
                $table->dropColumn('clinic');
            }
            if (Schema::hasColumn('doctor_profile', 'specialty')) {
                $table->dropColumn('specialty');
            }
            if (Schema::hasColumn('doctor_profile', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
