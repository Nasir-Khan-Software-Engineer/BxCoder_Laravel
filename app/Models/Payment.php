<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'order_id',        // foreign key to orders table
        'payment_method',  // string, required, e.g., 'cash', 'card', 'paypal'
        'payment_status',  // string, required, default: 'pending'
        'amount',          // decimal, required
        'transaction_id',  // nullable, string for online payments
        'paid_at',         // nullable, datetime when payment is completed
        'created_by',      // admin or system user id
        'updated_by',      // nullable, admin id
    ];

    protected $casts = [
        'order_id' => 'integer',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
