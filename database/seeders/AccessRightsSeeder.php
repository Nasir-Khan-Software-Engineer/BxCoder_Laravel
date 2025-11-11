<?php
namespace Database\Seeders;

use App\Models\AccessRights;
use Illuminate\Database\Seeder;

class AccessRightsSeeder extends Seeder
{
    public function run(): void
    {
        $rights = [

            // =================== USERS ===================
            [
                'route_name'        => 'api.admin.user.index',
                'short_id'          => 'user_list',
                'short_description' => 'View user list',
                'details'           => 'Allows viewing all registered users',
            ],
            [
                'route_name'        => 'api.admin.user.store',
                'short_id'          => 'user_create',
                'short_description' => 'Create a new user',
                'details'           => 'Allows creating a new user with assigned role',
            ],
            [
                'route_name'        => 'api.admin.user.update',
                'short_id'          => 'user_update',
                'short_description' => 'Update an existing user',
                'details'           => 'Allows updating profile and access of a user',
            ],
            [
                'route_name'        => 'api.admin.user.destroy',
                'short_id'          => 'user_delete',
                'short_description' => 'Delete a user',
                'details'           => 'Allows soft or hard deletion of a user entry',
            ],

            // =================== USER DETAILS ===================
            [
                'route_name'        => 'api.admin.user_details.index',
                'short_id'          => 'user_details_list',
                'short_description' => 'View all user profiles',
                'details'           => 'Allows listing extended user details and profiles',
            ],
            [
                'route_name'        => 'api.admin.user_details.store',
                'short_id'          => 'user_details_create',
                'short_description' => 'Create user details record',
                'details'           => 'Allows adding extended profile information for users',
            ],
            [
                'route_name'        => 'api.admin.user_details.update',
                'short_id'          => 'user_details_update',
                'short_description' => 'Update user profile details',
                'details'           => 'Allows updating additional user details data',
            ],
            [
                'route_name'        => 'api.admin.user_details.destroy',
                'short_id'          => 'user_details_delete',
                'short_description' => 'Delete user details record',
                'details'           => 'Allows deletion of extended profile information',
            ],

            // =================== ROLES ===================
            [
                'route_name'        => 'api.admin.role.index',
                'short_id'          => 'role_list',
                'short_description' => 'View role list',
                'details'           => 'Allows displaying all user roles available in system',
            ],
            [
                'route_name'        => 'api.admin.role.store',
                'short_id'          => 'role_create',
                'short_description' => 'Create new role',
                'details'           => 'Allows creation of new access roles',
            ],
            [
                'route_name'        => 'api.admin.role.update',
                'short_id'          => 'role_update',
                'short_description' => 'Update role details',
                'details'           => 'Allows changing name and setting of user roles',
            ],
            [
                'route_name'        => 'api.admin.role.destroy',
                'short_id'          => 'role_delete',
                'short_description' => 'Delete role',
                'details'           => 'Allows deleting a role unless assigned to users',
            ],

            // =================== ACCESS RIGHTS CRUD ===================
            [
                'route_name'        => 'api.admin.access_right.index',
                'short_id'          => 'access_right_list',
                'short_description' => 'List access rights',
                'details'           => 'Allows viewing all defined access rights',
            ],
            [
                'route_name'        => 'api.admin.access_right.store',
                'short_id'          => 'access_right_create',
                'short_description' => 'Create new access right',
                'details'           => 'Allows defining new access control permission',
            ],
            [
                'route_name'        => 'api.admin.access_right.update',
                'short_id'          => 'access_right_update',
                'short_description' => 'Update access right',
                'details'           => 'Allows modifying access right permissions and metadata',
            ],
            [
                'route_name'        => 'api.admin.access_right.destroy',
                'short_id'          => 'access_right_delete',
                'short_description' => 'Delete access right',
                'details'           => 'Allows removing unused access right rules',
            ],

            // =================== SITE FEATURE ===================
            [
                'route_name'        => 'api.admin.site_feature.index',
                'short_id'          => 'site_feature_list',
                'short_description' => 'View site feature list',
                'details'           => 'Allows viewing all site-wide features configured in the system.',
            ],
            [
                'route_name'        => 'api.admin.site_feature.store',
                'short_id'          => 'site_feature_create',
                'short_description' => 'Create a new site feature',
                'details'           => 'Allows adding new system-wide feature toggles and settings.',
            ],
            [
                'route_name'        => 'api.admin.site_feature.update',
                'short_id'          => 'site_feature_update',
                'short_description' => 'Update an existing site feature',
                'details'           => 'Allows modifying system feature states and associated metadata.',
            ],
            [
                'route_name'        => 'api.admin.site_feature.destroy',
                'short_id'          => 'site_feature_delete',
                'short_description' => 'Delete a site feature',
                'details'           => 'Allows removing a system feature configuration (only if not in use).',
            ],

            // =================== SETTINGS ===================
            [
                'route_name'        => 'api.admin.setting.index',
                'short_id'          => 'setting_list',
                'short_description' => 'View settings list',
                'details'           => 'Allows viewing all system configuration settings.',
            ],
            [
                'route_name'        => 'api.admin.setting.store',
                'short_id'          => 'setting_create',
                'short_description' => 'Create new setting',
                'details'           => 'Allows creating new system configuration items.',
            ],
            [
                'route_name'        => 'api.admin.setting.update',
                'short_id'          => 'setting_update',
                'short_description' => 'Update existing setting',
                'details'           => 'Allows modifying existing configuration values.',
            ],
            [
                'route_name'        => 'api.admin.setting.destroy',
                'short_id'          => 'setting_delete',
                'short_description' => 'Delete setting',
                'details'           => 'Allows removing system settings (use cautiously).',
            ],

            // =================== BRAND ===================
            [
                'route_name'        => 'api.admin.brand.index',
                'short_id'          => 'brand_list',
                'short_description' => 'View brand list',
                'details'           => 'Allows viewing all product brands.',
            ],
            [
                'route_name'        => 'api.admin.brand.store',
                'short_id'          => 'brand_create',
                'short_description' => 'Create new brand',
                'details'           => 'Allows adding new product brands.',
            ],
            [
                'route_name'        => 'api.admin.brand.update',
                'short_id'          => 'brand_update',
                'short_description' => 'Update brand',
                'details'           => 'Allows modifying brand information.',
            ],
            [
                'route_name'        => 'api.admin.brand.destroy',
                'short_id'          => 'brand_delete',
                'short_description' => 'Delete brand',
                'details'           => 'Allows deleting brands that are no longer required.',
            ],

            // =================== CATEGORY ===================
            [
                'route_name'        => 'api.admin.category.index',
                'short_id'          => 'category_list',
                'short_description' => 'View category list',
                'details'           => 'Allows viewing all product categories.',
            ],
            [
                'route_name'        => 'api.admin.category.store',
                'short_id'          => 'category_create',
                'short_description' => 'Create category',
                'details'           => 'Allows adding new product categories.',
            ],
            [
                'route_name'        => 'api.admin.category.update',
                'short_id'          => 'category_update',
                'short_description' => 'Update category',
                'details'           => 'Allows modifying existing product category information.',
            ],
            [
                'route_name'        => 'api.admin.category.destroy',
                'short_id'          => 'category_delete',
                'short_description' => 'Delete category',
                'details'           => 'Allows deleting categories that are no longer needed.',
            ],

            // =================== PRODUCTS ===================
            [
                'route_name'        => 'api.admin.product.index',
                'short_id'          => 'product_list',
                'short_description' => 'View product list',
                'details'           => 'Allows viewing all products in the system.',
            ],
            [
                'route_name'        => 'api.admin.product.store',
                'short_id'          => 'product_create',
                'short_description' => 'Create product',
                'details'           => 'Allows adding new products to the inventory.',
            ],
            [
                'route_name'        => 'api.admin.product.update',
                'short_id'          => 'product_update',
                'short_description' => 'Update product',
                'details'           => 'Allows modifying product details.',
            ],
            [
                'route_name'        => 'api.admin.product.destroy',
                'short_id'          => 'product_delete',
                'short_description' => 'Delete product',
                'details'           => 'Allows removing products from the catalog.',
            ],

            // =================== PRODUCT IMAGES ===================
            [
                'route_name'        => 'api.admin.product_image.index',
                'short_id'          => 'product_image_list',
                'short_description' => 'View product image list',
                'details'           => 'Allows viewing images associated with products.',
            ],
            [
                'route_name'        => 'api.admin.product_image.store',
                'short_id'          => 'product_image_create',
                'short_description' => 'Upload product image',
                'details'           => 'Allows adding new product images.',
            ],
            [
                'route_name'        => 'api.admin.product_image.update',
                'short_id'          => 'product_image_update',
                'short_description' => 'Update product image',
                'details'           => 'Allows modifying image records linked to products.',
            ],
            [
                'route_name'        => 'api.admin.product_image.destroy',
                'short_id'          => 'product_image_delete',
                'short_description' => 'Delete product image',
                'details'           => 'Allows removing product images from the gallery.',
            ],

            // =================== PRODUCT STOCK ===================
            [
                'route_name'        => 'api.admin.product_stock.index',
                'short_id'          => 'product_stock_list',
                'short_description' => 'View stock list',
                'details'           => 'Allows viewing product stock entries and inventory levels.',
            ],
            [
                'route_name'        => 'api.admin.product_stock.store',
                'short_id'          => 'product_stock_create',
                'short_description' => 'Add product stock',
                'details'           => 'Allows adding and recording new stock entries.',
            ],
            [
                'route_name'        => 'api.admin.product_stock.update',
                'short_id'          => 'product_stock_update',
                'short_description' => 'Update product stock',
                'details'           => 'Allows modifying existing stock information.',
            ],
            [
                'route_name'        => 'api.admin.product_stock.destroy',
                'short_id'          => 'product_stock_delete',
                'short_description' => 'Delete product stock entry',
                'details'           => 'Allows removing stock records from the inventory.',
            ],

            // =================== PROJECT ===================
            [
                'route_name'        => 'api.admin.project.index',
                'short_id'          => 'project_list',
                'short_description' => 'View project list',
                'details'           => 'Allows viewing all projects with their details such as title, keywords, description, and links.',
            ],
            [
                'route_name'        => 'api.admin.project.store',
                'short_id'          => 'project_create',
                'short_description' => 'Create a new project',
                'details'           => 'Allows adding a new project including title, keywords, short description, and links.',
            ],
            [
                'route_name'        => 'api.admin.project.update',
                'short_id'          => 'project_update',
                'short_description' => 'Update existing project',
                'details'           => 'Allows modifying an existing project’s information and links.',
            ],
            [
                'route_name'        => 'api.admin.project.destroy',
                'short_id'          => 'project_delete',
                'short_description' => 'Delete project',
                'details'           => 'Allows permanently removing a project from the system.',
            ],

// =================== POST ===================
            [
                'route_name'        => 'api.admin.post.index',
                'short_id'          => 'post_list',
                'short_description' => 'View all posts',
                'details'           => 'Allows viewing all posts including title, content, and related metadata.',
            ],
            [
                'route_name'        => 'api.admin.post.store',
                'short_id'          => 'post_create',
                'short_description' => 'Create a new post',
                'details'           => 'Allows adding a new post with title, content, and metadata.',
            ],
            [
                'route_name'        => 'api.admin.post.update',
                'short_id'          => 'post_update',
                'short_description' => 'Update post',
                'details'           => 'Allows modifying an existing post’s content and metadata.',
            ],
            [
                'route_name'        => 'api.admin.post.destroy',
                'short_id'          => 'post_delete',
                'short_description' => 'Delete post',
                'details'           => 'Allows permanently deleting a post from the system.',
            ],

            // =================== COMMENT ===================
            [
                'route_name'        => 'api.admin.comment.index',
                'short_id'          => 'comment_list',
                'short_description' => 'View all comments',
                'details'           => 'Allows viewing all user comments on posts or products.',
            ],
            [
                'route_name'        => 'api.admin.comment.store',
                'short_id'          => 'comment_create',
                'short_description' => 'Add a comment',
                'details'           => 'Allows adding a new comment to posts or products.',
            ],
            [
                'route_name'        => 'api.admin.comment.update',
                'short_id'          => 'comment_update',
                'short_description' => 'Update comment',
                'details'           => 'Allows editing an existing comment.',
            ],
            [
                'route_name'        => 'api.admin.comment.destroy',
                'short_id'          => 'comment_delete',
                'short_description' => 'Delete comment',
                'details'           => 'Allows removing a comment permanently from the system.',
            ],

// =================== REVIEW ===================
            [
                'route_name'        => 'api.admin.review.index',
                'short_id'          => 'review_list',
                'short_description' => 'View all reviews',
                'details'           => 'Allows viewing all reviews submitted by users on products.',
            ],
            [
                'route_name'        => 'api.admin.review.store',
                'short_id'          => 'review_create',
                'short_description' => 'Add a review',
                'details'           => 'Allows users to submit a new review for a product.',
            ],
            [
                'route_name'        => 'api.admin.review.update',
                'short_id'          => 'review_update',
                'short_description' => 'Update review',
                'details'           => 'Allows editing an existing product review.',
            ],
            [
                'route_name'        => 'api.admin.review.destroy',
                'short_id'          => 'review_delete',
                'short_description' => 'Delete review',
                'details'           => 'Allows permanently deleting a product review from the system.',
            ],

// =================== SUPPLIER ===================
            [
                'route_name'        => 'api.admin.supplier.index',
                'short_id'          => 'supplier_list',
                'short_description' => 'View suppliers',
                'details'           => 'Allows viewing all suppliers registered in the system.',
            ],
            [
                'route_name'        => 'api.admin.supplier.store',
                'short_id'          => 'supplier_create',
                'short_description' => 'Add a supplier',
                'details'           => 'Allows adding a new supplier to the system.',
            ],
            [
                'route_name'        => 'api.admin.supplier.update',
                'short_id'          => 'supplier_update',
                'short_description' => 'Update supplier',
                'details'           => 'Allows editing existing supplier information.',
            ],
            [
                'route_name'        => 'api.admin.supplier.destroy',
                'short_id'          => 'supplier_delete',
                'short_description' => 'Delete supplier',
                'details'           => 'Allows permanently removing a supplier from the system.',
            ],

// =================== COUPON ===================
            [
                'route_name'        => 'api.admin.coupon.index',
                'short_id'          => 'coupon_list',
                'short_description' => 'View coupons',
                'details'           => 'Allows viewing all discount coupons in the system.',
            ],
            [
                'route_name'        => 'api.admin.coupon.store',
                'short_id'          => 'coupon_create',
                'short_description' => 'Add a coupon',
                'details'           => 'Allows creating a new discount coupon.',
            ],
            [
                'route_name'        => 'api.admin.coupon.update',
                'short_id'          => 'coupon_update',
                'short_description' => 'Update coupon',
                'details'           => 'Allows editing existing coupon details.',
            ],
            [
                'route_name'        => 'api.admin.coupon.destroy',
                'short_id'          => 'coupon_delete',
                'short_description' => 'Delete coupon',
                'details'           => 'Allows permanently removing a coupon from the system.',
            ],

// =================== PAYMENT ===================
            [
                'route_name'        => 'api.admin.payment.index',
                'short_id'          => 'payment_list',
                'short_description' => 'View payments',
                'details'           => 'Allows viewing all payment transactions in the system.',
            ],
            [
                'route_name'        => 'api.admin.payment.store',
                'short_id'          => 'payment_create',
                'short_description' => 'Add a payment',
                'details'           => 'Allows recording a new payment transaction.',
            ],
            [
                'route_name'        => 'api.admin.payment.update',
                'short_id'          => 'payment_update',
                'short_description' => 'Update payment',
                'details'           => 'Allows modifying existing payment transaction details.',
            ],
            [
                'route_name'        => 'api.admin.payment.destroy',
                'short_id'          => 'payment_delete',
                'short_description' => 'Delete payment',
                'details'           => 'Allows permanently removing a payment transaction from the system.',
            ],

// =================== ORDER ===================
            [
                'route_name'        => 'api.admin.order.index',
                'short_id'          => 'order_list',
                'short_description' => 'View orders',
                'details'           => 'Allows viewing all orders placed by customers in the system.',
            ],
            [
                'route_name'        => 'api.admin.order.store',
                'short_id'          => 'order_create',
                'short_description' => 'Add an order',
                'details'           => 'Allows creating a new order manually.',
            ],
            [
                'route_name'        => 'api.admin.order.update',
                'short_id'          => 'order_update',
                'short_description' => 'Update order',
                'details'           => 'Allows editing existing order details, including status and items.',
            ],
            [
                'route_name'        => 'api.admin.order.destroy',
                'short_id'          => 'order_delete',
                'short_description' => 'Delete order',
                'details'           => 'Allows permanently removing an order from the system.',
            ],

// =================== ORDER ITEM ===================
            [
                'route_name'        => 'api.admin.order_item.index',
                'short_id'          => 'order_item_list',
                'short_description' => 'View order items',
                'details'           => 'Allows viewing all items associated with orders.',
            ],
            [
                'route_name'        => 'api.admin.order_item.store',
                'short_id'          => 'order_item_create',
                'short_description' => 'Add an order item',
                'details'           => 'Allows adding items to an existing order.',
            ],
            [
                'route_name'        => 'api.admin.order_item.update',
                'short_id'          => 'order_item_update',
                'short_description' => 'Update order item',
                'details'           => 'Allows modifying details of items within an order.',
            ],
            [
                'route_name'        => 'api.admin.order_item.destroy',
                'short_id'          => 'order_item_delete',
                'short_description' => 'Delete order item',
                'details'           => 'Allows removing an item from an order.',
            ],

        ];

        foreach ($rights as $right) {
            AccessRights::updateOrCreate(
                ['route_name' => $right['route_name']],
                $right
            );
        }
    }
}
