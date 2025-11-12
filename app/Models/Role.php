<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name', // required, min 5, max 100
        'description', // required, min 5, max 1000
        'is_active', // default true
        'is_default', // default false
        'created_by', // required foreign key
        'updated_by' // nullable foreign key
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
