<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', // required, unique, min: 3
        'keywords', // required, min: 3
        'short_description', // required, min: 3
        'details', // required, min: 3
        'source_code_link', // nullable
        'video_link', // nullable
        'documentation_link', // nullable
        'created_by', // required foreign key
        'updated_by' // nullable
    ];

    protected $casts = [
        'title' => 'string',
        'keywords' => 'string',
        'short_description' => 'string',
        'details' => 'string',
        'source_code_link' => 'string',
        'video_link' => 'string',
        'documentation_link' => 'string',
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

    // Many-to-many relation with categories
    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }
}
