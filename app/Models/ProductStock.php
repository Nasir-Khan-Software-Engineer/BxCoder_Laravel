<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    protected $table = 'product_stocks';

    // fillable
    protected $fillable = [
        'product_id', // foriegn key product id
        'quantity', // not null, integer, min value 1
        
        'buying_price', // not null, decimal
        'selling_price', // not null, decimal
        
        'supplier_id', // foriegn key supplier id
        
        'discount_type', // not null, enum ['fixed', 'percentage']
        'discount_value', // not null, decimal

        'created_by', // not null, foriegn key 
        'updated_by', // not null, foriegn key
        'is_active', // not null, boolean false / 1
    ];

    protected $casts = [
        'quantity' => 'integer',
        'buying_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // product 
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // supplier 
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // orders 
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // creator 
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // updater 
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
