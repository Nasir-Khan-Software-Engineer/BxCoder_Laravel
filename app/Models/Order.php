<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    // fillable
    protected $fillable = [
        'invoice_no', // unique, not null, min: 10 max: 100 characters
        'order_date', // not null
        
        'user_id', // not null, foreign key, customer user
        
        'total_price', // not null, decimal
        'total_items',  // not null, integer
        'total_qty', // not null, integer
        
        'status', // not null, default: pending
        'note', // text, nullable
        
        'is_inside_dhaka', // not null, default: false
        'shipping_cost', // not null, decimal
        
        'payment_method', // not null, default: cash
        'payment_status', // not null, default: pending
        'address', // not null, text
        
        'updated_by', // not null, foreign key, admin user
    ];

    protected $casts = [
        'user_id' => 'integer',
        'total_price' => 'decimal:2',
        'total_items' => 'integer',
        'total_qty' => 'integer',
        'status' => 'string',
        'is_inside_dhaka' => 'boolean',
        'shipping_cost' => 'decimal:2',
        'payment_method' => 'string',
        'payment_status' => 'string',
        'updated_by' => 'integer',
        'order_date' => 'datetime',
    ];

    // customer user where type is customer
    public function customer()
    {
        return $this->belongsTo(UserDetails::class, 'user_id');
    }

    // admin user
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // payments
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // order items
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
