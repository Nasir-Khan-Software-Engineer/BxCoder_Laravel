<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Review extends Model
{
    protected $table = 'reviews';

    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'comment',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'status' => 'boolean',
        'comment' => 'string',
    ];

    // Product relationship
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // User relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
