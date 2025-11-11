<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'name',          // required, string, min 3, max 200
        'email',         // required, string, email, max 200
        'phone',         // required, string, max 20
        'address',       // nullable, string, max 1000
        'status',        // required, boolean, default 1 (active)
        'created_by',    // required, foreign key (user_id)
        'updated_by',    // nullable, foreign key (user_id)
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    // Creator relation
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Updater relation
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Products supplied by this supplier
    public function productStocks()
    {
        return $this->hasMany(ProductStock::class);
    }
}
