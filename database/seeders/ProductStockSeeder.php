<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductStock;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;

class ProductStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();      // adjust how many products you want
        $suppliers = Supplier::take(3)->get();    // get some suppliers
        $users = User::take(2)->get();            // creator/updater users

        foreach ($products as $product) {
            foreach ($suppliers as $supplier) {
                ProductStock::create([
                    'product_id' => $product->id,
                    'quantity' => rand(10, 100),
                    'buying_price' => rand(50, 200),
                    'selling_price' => rand(200, 500),
                    'supplier_id' => $supplier->id,
                    'discount_type' => 'percentage',
                    'discount_value' => rand(5, 20),
                    'created_by' => $users->first()->id,
                    'updated_by' => $users->last()->id,
                    'is_active' => true,
                ]);
            }
        }
    }
}
