<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\User;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a user to set as created_by (assuming at least one user exists)
        $adminUser = User::first();

        $suppliers = [
            [
                'name' => 'Global Supplies Ltd.',
                'email' => 'info@globalsupplies.com',
                'phone' => '01710000001',
                'address' => 'Dhaka, Bangladesh',
                'status' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Tech Warehouse',
                'email' => 'contact@techwarehouse.com',
                'phone' => '01710000002',
                'address' => 'Chittagong, Bangladesh',
                'status' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Prime Electronics',
                'email' => 'sales@primeelectronics.com',
                'phone' => '01710000003',
                'address' => 'Sylhet, Bangladesh',
                'status' => true,
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
