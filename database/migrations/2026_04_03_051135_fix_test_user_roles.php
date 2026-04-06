<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::update("UPDATE users SET role = 'admin' WHERE email = 'admin@test.com'");
        DB::update("UPDATE users SET role = 'doctor' WHERE email = 'doctor@test.com'");
        DB::update("UPDATE users SET role = 'guardian' WHERE email = 'guardian@test.com'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::update("UPDATE users SET role = 'Admin' WHERE email = 'admin@test.com'");
        DB::update("UPDATE users SET role = 'Doctor' WHERE email = 'doctor@test.com'");
        DB::update("UPDATE users SET role = 'Guardian' WHERE email = 'guardian@test.com'");
    }
};
