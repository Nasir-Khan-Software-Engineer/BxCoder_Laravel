<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use App\Models\User;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::first(); // Make sure at least one user exists

        $coupons = [
            [
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order_amount' => 500.00,
                'max_discount_amount' => 100.00,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonth(),
                'usage_limit' => 100,
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'code' => 'FLAT50',
                'type' => 'fixed',
                'value' => 50.00,
                'min_order_amount' => 300.00,
                'max_discount_amount' => null,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonth(),
                'usage_limit' => 50,
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'code' => 'HOLIDAY20',
                'type' => 'percentage',
                'value' => 20.00,
                'min_order_amount' => 1000.00,
                'max_discount_amount' => 200.00,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(2),
                'usage_limit' => 200,
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::create($coupon);
        }
    }
}
