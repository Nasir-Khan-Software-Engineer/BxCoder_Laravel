<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Product;
use App\Models\User;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::take(3)->get();      // assuming 3 users exist
        $products = Product::take(5)->get(); // assuming 5 products

        foreach ($products as $product) {
            foreach ($users as $user) {
                Review::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'rating' => rand(1, 5),
                    'comment' => "This is a review by {$user->name} on {$product->name}.",
                    'status' => true,
                ]);
            }
        }
    }
}
