<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // =================== ADMIN USER ===================
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'role_id' => 1, // Admin role
                'type' => 'admin',
            ]
        );

        // =================== SALESPERSON ===================
        User::updateOrCreate(
            ['email' => 'sales@example.com'],
            [
                'name' => 'Salesperson User',
                'email' => 'sales@example.com',
                'password' => Hash::make('password123'),
                'role_id' => 2, // Salesperson role
                'type' => 'salesperson',
            ]
        );

        // =================== CUSTOMER ===================
        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer User',
                'email' => 'customer@example.com',
                'password' => Hash::make('password123'),
                'role_id' => 3, // Customer role
                'type' => 'customer',
            ]
        );
    }
}
