<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', // required, unique, min:3, max: 1000
        'slug', // required, unique (min:3, max:1000)
        
        'keywords', // required, min:3, max:1000
        'description', // required, min:3, max:1000
        
        'image', // nullable, base64 string
        'body', // required, min:3

        'project_url', // nullable
        'video_url', // nullable
        'code_url', // nullable
        
        'created_by', // required, foreign key, user
        'updated_by', // nullable, foreign key, user
        
        'is_active', // required, default: 0
    ];

    protected $casts = [
        'title' => 'string',
        'slug' => 'string',
        'keywords' => 'string',
        'description' => 'string',
        'image' => 'string',
        'body' => 'string',
        'project_url' => 'string',
        'video_url' => 'string',
        'code_url' => 'string',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relation to creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relation to updater
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function comments()
    {
        return $this->belongsToMany(Comment::class)->withTimestamps();
    }
}
