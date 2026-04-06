<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test guardian
        User::firstOrCreate(
            ['email' => 'guardian@test.com'],
            [
                'name' => 'Test Guardian',
                'password_hash' => Hash::make('Password123'),
                'role' => 'guardian',
            ]
        );

        // Create test admin
        User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Test Admin',
                'password_hash' => Hash::make('Password123'),
                'role' => 'admin',
            ]
        );

        // Create test doctor
        User::firstOrCreate(
            ['email' => 'doctor@test.com'],
            [
                'name' => 'Test Doctor',
                'password_hash' => Hash::make('Password123'),
                'role' => 'doctor',
            ]
        );
    }
}
