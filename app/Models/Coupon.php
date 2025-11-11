<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'coupons';

    protected $fillable = [
        'code',           // string, required, unique
        'type',           // string, enum: 'fixed', 'percentage'
        'value',          // decimal, required, discount value
        'min_order_amount', // decimal, optional, minimum order for coupon to apply
        'max_discount_amount', // decimal, optional, max discount allowed
        'start_date',     // datetime, required
        'end_date',       // datetime, required
        'usage_limit',    // integer, nullable, total number of uses
        'used_count',     // integer, default 0, number of times used
        'is_active',      // boolean, default true
        'created_by',     // foreign key, user/admin
        'updated_by',     // nullable, foreign key
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
