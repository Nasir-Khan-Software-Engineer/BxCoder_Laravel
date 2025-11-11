<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'brands';

    protected $fillable = [
        'name', // not null, unique, max: 200 char, min : 3 char, index
        'slug', // not null, unique, max: 300 char, index
        'image', // null, base64 string
        'status', // not null, default: 1
        'keywords', // not null, max: 1000 char, min : 3 char
        'description', // not null, max: 1000 char, min : 3 char
        'created_by', // user
        'updated_by', // user
    ];


    protected $casts = [
        'status' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
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
