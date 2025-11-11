<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create Admin role
        Role::create([
            'name' => 'Admin',
            'description' => 'Administrator with full access to all modules.',
            'is_active' => true,
            'is_default' => false,
            'created_by' => 1, // assuming user id 1 is super admin
        ]);

        // Create Salesperson role
        Role::create([
            'name' => 'Salesperson',
            'description' => 'Salesperson with limited access to sales related modules.',
            'is_active' => true,
            'is_default' => false,
            'created_by' => 1,
        ]);
    }
}
