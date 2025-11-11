<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', // required, unique, max: 200 characters, min: 3 characters, index
        'slug', // required, unique, max: 300 characters, min: 3 characters, index
        'image', // null, base64 encoded string
        'status', // required, default: 1
        'keywords', // not null, max: 1000 characters, min: 3 characters
        'description', // not null, max: 1000 characters, min: 3 characters
        'created_by', // user id
        'updated_by', // user id
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    // Category created by user
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Category updated by user
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function posts()
    {
        return $this->belongsToMany(Post::class)->withTimestamps();
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

}
