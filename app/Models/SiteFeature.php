<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteFeature extends Model
{
    protected $table = 'site_features';

    // fillable, name 
    protected $fillable = [
        'name', // string, required, unique, index
        'is_default', // boolean, required, false
        'is_active', // boolean, required, false
        'created_by', // foreign key
        'updated_by', // null or foreign key
    ];
    
    protected $casts = [
        'name' => 'string',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
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

}
