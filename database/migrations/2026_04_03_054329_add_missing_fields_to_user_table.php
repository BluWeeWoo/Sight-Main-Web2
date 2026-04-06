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
        Schema::table('user', function (Blueprint $table) {
            if (!Schema::hasColumn('user', 'name')) {
                $table->string('name')->nullable()->after('email');
            }
            if (!Schema::hasColumn('user', 'phone')) {
                $table->string('phone')->nullable()->after('role');
            }
            if (!Schema::hasColumn('user', 'specialty')) {
                $table->string('specialty')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('user', 'clinic')) {
                $table->string('clinic')->nullable()->after('specialty');
            }
            if (!Schema::hasColumn('user', 'license_number')) {
                $table->string('license_number')->nullable()->after('clinic');
            }
            if (!Schema::hasColumn('user', 'location')) {
                $table->string('location')->nullable()->after('license_number');
            }
            if (!Schema::hasColumn('user', 'status')) {
                $table->string('status')->default('active')->after('location');
            }
            if (!Schema::hasColumn('user', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumnIfExists(['name', 'phone', 'specialty', 'clinic', 'license_number', 'location', 'status', 'email_verified_at']);
        });
    }
};
