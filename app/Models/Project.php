<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'keywords',
        'short_description',
        'details',
        'source_code_link',
        'video_link',
        'documentation_link',
        'created_by',
        'updated_by'
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
