<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        
        $this->call(RoleSeeder::class);           // Roles
        $this->call(AccessRightsSeeder::class);   // Access rights

        // Users and settings
        $this->call(UserSeeder::class);
        $this->call(UserDetailsSeeder::class);
        $this->call(SettingSeeder::class);

        // Brands and categories
        $this->call(BrandSeeder::class);
        $this->call(CategorySeeder::class);

        // Products and related
        $this->call(ProductSeeder::class);
        $this->call(ProductImageSeeder::class);
        $this->call(ProductStockSeeder::class);

        // Content
        $this->call(ProjectSeeder::class);
        $this->call(PostSeeder::class);
        $this->call(CommentSeeder::class);
        $this->call(ReviewSeeder::class);

        // E-commerce
        $this->call(SupplierSeeder::class);
        $this->call(CouponSeeder::class);
        $this->call(PaymentSeeder::class);
        $this->call(OrderSeeder::class);
        $this->call(OrderItemSeeder::class);

        $this->call(SiteFeatureSeeder::class);    // Main site features
        
    }
}
