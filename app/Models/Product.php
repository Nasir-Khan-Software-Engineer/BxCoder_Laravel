<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    // fillable
    protected $fillable = [
        'name',  // required, unique, min: 3
        'slug',  // required, unique, min: 3
        'code', // required, unique, min: 3
        'details', // required, min: 3 

        'keywords', // required, min: 3
        'short_description', // required, min: 3
         
        'brand_id', // required, foreign key to brands table
        'unit_id',  // required, foreign key to units table
        
        'created_by',  // required, foreign key to users table
        'updated_by', // nullable, foreign key to users table

        'status', // required, default: pending
        'is_active' // required, default: 0
    ];

    protected $casts = [
        'brand_id' => 'integer',
        'unit_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'status' => 'string',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // reviews 
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // orders items
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

    // stocks 
    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    // images 
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

}
