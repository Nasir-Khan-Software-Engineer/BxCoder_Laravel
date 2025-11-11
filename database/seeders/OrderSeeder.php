<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Models\ProductStock;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('role_id', 3)->first(); // Example customer user
        $productStocks = ProductStock::all();

        if (!$user || $productStocks->isEmpty()) {
            $this->command->info('No users or product stocks found. Seed them first.');
            return;
        }

        // Create 5 sample orders
        for ($i = 1; $i <= 5; $i++) {
            $selectedStocks = $productStocks->random(2); // pick 2 random product stocks

            $totalQty = $selectedStocks->sum('quantity');
            $totalPrice = $selectedStocks->sum(fn($stock) => $stock->selling_price * $stock->quantity);

            $order = Order::create([
                'invoice_no' => 'INV-' . Str::upper(Str::random(6)),
                'order_date' => Carbon::now(),
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'total_items' => $selectedStocks->count(),
                'total_qty' => $totalQty,
                'status' => 'pending',
                'note' => 'Sample order note',
                'is_inside_dhaka' => true,
                'shipping_cost' => 50.00,
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'address' => 'Dhaka, Bangladesh',
                'updated_by' => $user->id,
            ]);

            // Create Order Items
            foreach ($selectedStocks as $stock) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_stock_id' => $stock->id,
                    'product_name' => $stock->product->name,
                    'discount_type' => $stock->discount_type,
                    'discount_value' => $stock->discount_value,
                    'quantity' => $stock->quantity,
                    'unit_price' => $stock->selling_price,
                    'total_price' => $stock->selling_price * $stock->quantity,
                ]);
            }

            // Create Payment for this order
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'amount' => $totalPrice + 50.00, // include shipping cost
                'transaction_id' => null,
                'paid_at' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        }
    }
}
