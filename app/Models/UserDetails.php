<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    protected $table = 'user_details';

    protected $fillable = [
        'user_id', // Foreign key to the users table
        'address_1', // required, min 3 , max 1000 
        'address_2' // null , min 3 , max 1000
    ];

    protected $casts = [
        'address_1' => 'string',
        'address_2' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
