<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'Apple', 'description' => 'Apple products including iPhones, Macs, and accessories.'],
            ['name' => 'Samsung', 'description' => 'Samsung electronics and mobile devices.'],
            ['name' => 'Sony', 'description' => 'Sony electronics, TVs, and audio devices.'],
            ['name' => 'Dell', 'description' => 'Dell laptops, desktops, and monitors.'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['name' => $brand['name']],
                [
                    'description' => $brand['description'],
                    'slug' => Str::slug($brand['name']),
                    'keywords' => $brand['name'],
                    'status' => true,
                    'created_by' => 1,
                    'updated_by' => null,
                ]
            );
        }
    }
}
