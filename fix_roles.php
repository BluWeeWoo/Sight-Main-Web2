<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Update specific users directly
DB::table('user')->where('email', 'admin@lumi.com')->update(['role' => 'admin']);
DB::table('user')->where('email', 'doctor@lumi.com')->update(['role' => 'doctor']);
DB::table('user')->where('email', 'doctor2@lumi.com')->update(['role' => 'doctor']);

echo "Updated all roles to lowercase!\n";

$users = DB::table('user')->select('user_id', 'email', 'role')->get();
echo "\nVerifying updated users:\n";
foreach ($users as $user) {
    echo "Email: {$user->email}, Role: {$user->role}\n";
}

?>
