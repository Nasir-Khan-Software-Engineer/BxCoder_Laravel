<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $table = 'product_images';

    protected $fillable = [
        'product_id', // Foreign key, referencing the 'id' column in the 'products' table
        'image', // not null, based64 image
        'alt', // not null, minimum 3 characters
        'title', // not null, minimum 3 characters
        'style', // null or minimum 3 characters
    ];

    protected $casts = [
        'image' => 'string',
        'alt' => 'string',
        'title' => 'string',
        'style' => 'string',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
