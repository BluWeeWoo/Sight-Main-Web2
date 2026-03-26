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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('doctor')->after('password');
            $table->string('phone')->nullable()->after('role');
            $table->string('clinic')->nullable()->after('phone');
            $table->string('location')->nullable()->after('clinic');
            $table->string('status')->default('active')->after('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'clinic', 'location', 'status']);
        });
    }
};
