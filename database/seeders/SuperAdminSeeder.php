<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'wirawan.aang5@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('super123'),
                'role' => 'superadmin',
                'email_verified_at' => now(),
            ]
        );
    }
}
