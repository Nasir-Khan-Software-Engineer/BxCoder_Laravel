<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'keywords',
        'description',
        'created_by',
        'updated_by',
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

}
