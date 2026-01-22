<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@mikrotik.com',
            'password' => Hash::make('admin123'), // Password login nanti
            'role' => 'superadmin',
            'is_active' => '1',
            'email_verified_at' => now(),
        ]);
    }
}