<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id', // Foreign key to the orders table
        'product_id', // Foreign key to the products table
        'product_name', // Name of the product
        'discount_type', // Discount type, fixed and percentage
        'discount_value', // null if no discount, decimal 
        'quantity', // Quantity of the product
        'unit_price', // Price of the product
        'total_price', // Total price of the product
    ];

    protected $casts = [
        'order_id' => 'integer',
        'product_id' => 'integer',
        'discount_value' => 'decimal:2',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
