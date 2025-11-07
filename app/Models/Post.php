<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'keywords',
        'description',
        'image',
        'body',
        'project_url',
        'created_by',
        'updated_by',
        'is_active',
        'video_url',
        'code_url',
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

}
