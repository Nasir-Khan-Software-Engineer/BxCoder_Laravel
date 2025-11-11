<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductImage;
use App\Models\Product;

class ProductImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch some products to attach images
        $products = Product::take(5)->get(); // adjust how many products you want

        foreach ($products as $product) {
            ProductImage::create([
                'product_id' => $product->id,
                'image' => 'images/sample-product.jpg', // you can use placeholder path or base64
                'alt' => 'Sample image for ' . $product->name,
                'title' => $product->name . ' Image',
                'style' => null,
            ]);
        }
    }
}
