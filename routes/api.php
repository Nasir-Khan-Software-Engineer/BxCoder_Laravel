<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SiteFeatureController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDetailsController;
use App\Http\Controllers\AccessRightsController;

use Illuminate\Support\Facades\Route;

// Public routes
Route::post('register', [AuthController::class, 'register'])->name('api.auth.register');
Route::post('login', [AuthController::class, 'login'])->name('api.auth.login');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [AuthController::class, 'logout'])->name('api.auth.logout');

    // Users
    Route::get('user-list', [UserController::class, 'index'])->name('api.admin.user.index');
    Route::post('user-create', [UserController::class, 'store'])->name('api.admin.user.store');
    Route::put('user-update/{id}', [UserController::class, 'update'])->name('api.admin.user.update');
    Route::delete('user-delete/{id}', [UserController::class, 'destroy'])->name('api.admin.user.destroy');

    Route::get('user-details-list', [UserDetailsController::class, 'index'])->name('api.admin.user_details.index');
    Route::post('user-details-create', [UserDetailsController::class, 'store'])->name('api.admin.user_details.store');
    Route::put('user-details-update/{id}', [UserDetailsController::class, 'update'])->name('api.admin.user_details.update');
    Route::delete('user-details-delete/{id}', [UserDetailsController::class, 'destroy'])->name('api.admin.user_details.destroy');

    // Roles
    Route::get('role-list', [RoleController::class, 'index'])->name('api.admin.role.index');
    Route::post('role-create', [RoleController::class, 'store'])->name('api.admin.role.store');
    Route::put('role-update/{id}', [RoleController::class, 'update'])->name('api.admin.role.update');
    Route::delete('role-delete/{id}', [RoleController::class, 'destroy'])->name('api.admin.role.destroy');

    // AccessRights
    Route::get('access-right-list', [AccessRightsController::class, 'index'])->name('api.admin.access_right.index');
    Route::post('access-right-create', [AccessRightsController::class, 'store'])->name('api.admin.access_right.store');
    Route::put('access-right-update/{id}', [AccessRightsController::class, 'update'])->name('api.admin.access_right.update');
    Route::delete('access-right-delete/{id}', [AccessRightsController::class, 'destroy'])->name('api.admin.access_right.destroy');

    // SiteFeature
    Route::get('site-feature-list', [SiteFeatureController::class, 'index'])->name('api.admin.site_feature.index');
    Route::post('site-feature-create', [SiteFeatureController::class, 'store'])->name('api.admin.site_feature.store');
    Route::put('site-feature-update/{id}', [SiteFeatureController::class, 'update'])->name('api.admin.site_feature.update');
    Route::delete('site-feature-delete/{id}', [SiteFeatureController::class, 'destroy'])->name('api.admin.site_feature.destroy');

    // Settings
    Route::get('setting-list', [SettingController::class, 'index'])->name('api.admin.setting.index');
    Route::post('setting-create', [SettingController::class, 'store'])->name('api.admin.setting.store');
    Route::put('setting-update/{id}', [SettingController::class, 'update'])->name('api.admin.setting.update');
    Route::delete('setting-delete/{id}', [SettingController::class, 'destroy'])->name('api.admin.setting.destroy');

    // Brands
    Route::get('brand-list', [BrandController::class, 'index'])->name('api.admin.brand.index');
    Route::post('brand-create', [BrandController::class, 'store'])->name('api.admin.brand.store');
    Route::put('brand-update/{id}', [BrandController::class, 'update'])->name('api.admin.brand.update');
    Route::delete('brand-delete/{id}', [BrandController::class, 'destroy'])->name('api.admin.brand.destroy');

    // Categories
    Route::get('category-list', [CategoryController::class, 'index'])->name('api.admin.category.index');
    Route::post('category-create', [CategoryController::class, 'store'])->name('api.admin.category.store');
    Route::put('category-update/{id}', [CategoryController::class, 'update'])->name('api.admin.category.update');
    Route::delete('category-delete/{id}', [CategoryController::class, 'destroy'])->name('api.admin.category.destroy');

    // Products
    Route::get('product-list', [ProductController::class, 'index'])->name('api.admin.product.index');
    Route::post('product-create', [ProductController::class, 'store'])->name('api.admin.product.store');
    Route::put('product-update/{id}', [ProductController::class, 'update'])->name('api.admin.product.update');
    Route::delete('product-delete/{id}', [ProductController::class, 'destroy'])->name('api.admin.product.destroy');

    Route::get('product-image-list', [ProductImageController::class, 'index'])->name('api.admin.product_image.index');
    Route::post('product-image-create', [ProductImageController::class, 'store'])->name('api.admin.product_image.store');
    Route::put('product-image-update/{id}', [ProductImageController::class, 'update'])->name('api.admin.product_image.update');
    Route::delete('product-image-delete/{id}', [ProductImageController::class, 'destroy'])->name('api.admin.product_image.destroy');

    Route::get('product-stock-list', [ProductStockController::class, 'index'])->name('api.admin.product_stock.index');
    Route::post('product-stock-create', [ProductStockController::class, 'store'])->name('api.admin.product_stock.store');
    Route::put('product-stock-update/{id}', [ProductStockController::class, 'update'])->name('api.admin.product_stock.update');
    Route::delete('product-stock-delete/{id}', [ProductStockController::class, 'destroy'])->name('api.admin.product_stock.destroy');

    // Projects
    Route::get('project-list', [ProjectController::class, 'index'])->name('api.admin.project.index');
    Route::post('project-create', [ProjectController::class, 'store'])->name('api.admin.project.store');
    Route::put('project-update/{id}', [ProjectController::class, 'update'])->name('api.admin.project.update');
    Route::delete('project-delete/{id}', [ProjectController::class, 'destroy'])->name('api.admin.project.destroy');

    // Posts
    Route::get('post-list', [PostController::class, 'index'])->name('api.admin.post.index');
    Route::post('post-create', [PostController::class, 'store'])->name('api.admin.post.store');
    Route::put('post-update/{id}', [PostController::class, 'update'])->name('api.admin.post.update');
    Route::delete('post-delete/{id}', [PostController::class, 'destroy'])->name('api.admin.post.destroy');

    // Comments
    Route::get('comment-list', [CommentController::class, 'index'])->name('api.admin.comment.index');
    Route::post('comment-create', [CommentController::class, 'store'])->name('api.admin.comment.store');
    Route::put('comment-update/{id}', [CommentController::class, 'update'])->name('api.admin.comment.update');
    Route::delete('comment-delete/{id}', [CommentController::class, 'destroy'])->name('api.admin.comment.destroy');

    // Reviews
    Route::get('review-list', [ReviewController::class, 'index'])->name('api.admin.review.index');
    Route::post('review-create', [ReviewController::class, 'store'])->name('api.admin.review.store');
    Route::put('review-update/{id}', [ReviewController::class, 'update'])->name('api.admin.review.update');
    Route::delete('review-delete/{id}', [ReviewController::class, 'destroy'])->name('api.admin.review.destroy');

    // Suppliers
    Route::get('supplier-list', [SupplierController::class, 'index'])->name('api.admin.supplier.index');
    Route::post('supplier-create', [SupplierController::class, 'store'])->name('api.admin.supplier.store');
    Route::put('supplier-update/{id}', [SupplierController::class, 'update'])->name('api.admin.supplier.update');
    Route::delete('supplier-delete/{id}', [SupplierController::class, 'destroy'])->name('api.admin.supplier.destroy');

    // Coupons
    Route::get('coupon-list', [CouponController::class, 'index'])->name('api.admin.coupon.index');
    Route::post('coupon-create', [CouponController::class, 'store'])->name('api.admin.coupon.store');
    Route::put('coupon-update/{id}', [CouponController::class, 'update'])->name('api.admin.coupon.update');
    Route::delete('coupon-delete/{id}', [CouponController::class, 'destroy'])->name('api.admin.coupon.destroy');

    // Payments
    Route::get('payment-list', [PaymentController::class, 'index'])->name('api.admin.payment.index');
    Route::post('payment-create', [PaymentController::class, 'store'])->name('api.admin.payment.store');
    Route::put('payment-update/{id}', [PaymentController::class, 'update'])->name('api.admin.payment.update');
    Route::delete('payment-delete/{id}', [PaymentController::class, 'destroy'])->name('api.admin.payment.destroy');

    // Orders
    Route::get('order-list', [OrderController::class, 'index'])->name('api.admin.order.index');
    Route::post('order-create', [OrderController::class, 'store'])->name('api.admin.order.store');
    Route::put('order-update/{id}', [OrderController::class, 'update'])->name('api.admin.order.update');
    Route::delete('order-delete/{id}', [OrderController::class, 'destroy'])->name('api.admin.order.destroy');

    // Order Items
    Route::get('order-item-list', [OrderItemController::class, 'index'])->name('api.admin.order_item.index');
    Route::post('order-item-create', [OrderItemController::class, 'store'])->name('api.admin.order_item.store');
    Route::put('order-item-update/{id}', [OrderItemController::class, 'update'])->name('api.admin.order_item.update');
    Route::delete('order-item-delete/{id}', [OrderItemController::class, 'destroy'])->name('api.admin.order_item.destroy');
});
