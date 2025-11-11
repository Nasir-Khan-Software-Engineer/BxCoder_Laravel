<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessRights extends Model
{
    protected $table = 'access_rights';
    // fillable
    protected $fillable = [
        'route_name', // required string, min 5, max 200
        'short_id', //required  string , min 5, max 200
        'short_description', // required string , min 5, max 300
        'details', // null, max 1000, min: 10
    ];

    protected $casts = [
        'route_name' => 'string',
        'short_id' => 'string',
        'short_description' => 'string',
        'details' => 'string',
    ];
}
