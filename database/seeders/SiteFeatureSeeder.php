<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteFeature;
use App\Models\User;

class SiteFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role_id', 1)->first(); // Admin user
        if (!$admin) {
            $this->command->info('No admin user found. Seed users first.');
            return;
        }

        $features = [
            [
                'name' => 'Feature One',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Feature Two',
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Feature Three',
                'is_default' => false,
                'is_active' => false,
            ],
        ];

        foreach ($features as $feature) {
            SiteFeature::create(array_merge($feature, [
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]));
        }
    }
}
