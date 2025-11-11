<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Laptops', 'description' => 'All types of laptops and notebooks.'],
            ['name' => 'Smartphones', 'description' => 'Smartphones from various brands.'],
            ['name' => 'Headphones', 'description' => 'Audio devices including headphones and earphones.'],
            ['name' => 'Monitors', 'description' => 'Computer monitors and displays.'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'slug' => Str::slug($category['name']),
                    'keywords' => $category['name'],
                    'status' => true,
                    'created_by' => 1,
                    'updated_by' => null,
                ]
            );
        }
    }
}
