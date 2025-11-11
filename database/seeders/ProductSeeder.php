<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'MacBook Pro 16"',
                'code' => 'MBP16-2025',
                'details' => 'Apple MacBook Pro 16 inch with M1 Pro chip, 16GB RAM, 1TB SSD.',
                'keywords' => 'macbook, apple, laptop',
                'short_description' => 'High-performance Apple laptop',
                'brand_name' => 'Apple',
                'categories' => ['Laptops'],
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Samsung Galaxy S23',
                'code' => 'SGS23-2025',
                'details' => 'Samsung Galaxy S23 smartphone with 8GB RAM, 256GB storage.',
                'keywords' => 'samsung, smartphone, galaxy',
                'short_description' => 'Latest Samsung smartphone',
                'brand_name' => 'Samsung',
                'categories' => ['Smartphones'],
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'code' => 'SONY-HM5',
                'details' => 'Sony over-ear noise-canceling headphones, wireless.',
                'keywords' => 'sony, headphones, audio',
                'short_description' => 'Top-notch noise-canceling headphones',
                'brand_name' => 'Sony',
                'categories' => ['Headphones'],
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Dell UltraSharp Monitor',
                'code' => 'DELL-U2723',
                'details' => 'Dell UltraSharp 27 inch 4K monitor for professionals.',
                'keywords' => 'dell, monitor, display',
                'short_description' => 'Professional 4K monitor',
                'brand_name' => 'Dell',
                'categories' => ['Monitors'],
                'status' => 'active',
                'is_active' => true,
            ],
        ];

        foreach ($products as $data) {
            // Find brand
            $brand = Brand::where('name', $data['brand_name'])->first();

            if (!$brand) {
                $this->command->info('No brands found. Seed them first.');

                continue; // skip if brand not found
            }

            // Create or update product
            $product = Product::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'details' => $data['details'],
                    'keywords' => $data['keywords'],
                    'short_description' => $data['short_description'],
                    'brand_id' => $brand->id,
                    'created_by' => 1,
                    'updated_by' => null,
                    'status' => $data['status'],
                    'is_active' => $data['is_active'],
                ]
            );

            // Attach categories
            $category_ids = Category::whereIn('name', $data['categories'])->pluck('id')->toArray();
            $product->categories()->sync($category_ids);
        }
    }
}
