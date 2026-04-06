<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Get users from database - raw query
$users = DB::select("SELECT user_id, email, role, PASSWORD_hash FROM user");

echo "Users in database (raw):\n";
foreach ($users as $user) {
    echo "ID: {$user->user_id}, Email: {$user->email}, Role: '{$user->role}' (length: " . strlen($user->role) . "), Has Password: " . (!empty($user->PASSWORD_hash) ? 'YES' : 'NO') . "\n";
}

// Test password verification
echo "\n\nTesting password verification:\n";
$testPassword = 'password123';
$admin = DB::table('user')->where('email', 'admin@lumi.com')->first();

if ($admin) {
    echo "Found admin user: {$admin->email}\n";
    echo "Password hash: " . substr($admin->password_hash, 0, 20) . "...\n";
    echo "Password matches: " . (Hash::check($testPassword, $admin->password_hash) ? 'YES' : 'NO') . "\n";
} else {
    echo "Admin user not found!\n";
}

?>
