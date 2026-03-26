<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@lumi.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '+234123456789',
                'status' => 'active',
            ]
        );

        // Create doctor user
        User::firstOrCreate(
            ['email' => 'doctor@lumi.com'],
            [
                'name' => 'Dr. Martinez',
                'password' => Hash::make('password123'),
                'role' => 'doctor',
                'phone' => '+234987654321',
                'clinic' => 'Martha Eye Center',
                'location' => 'Lagos, NG',
                'status' => 'active',
            ]
        );

        // Create additional test doctors
        User::firstOrCreate(
            ['email' => 'doctor2@lumi.com'],
            [
                'name' => 'Dr. Sarah Smith',
                'password' => Hash::make('password123'),
                'role' => 'doctor',
                'phone' => '+234111222333',
                'clinic' => 'Central Medical',
                'location' => 'Abuja, NG',
                'status' => 'active',
            ]
        );
    }
}
