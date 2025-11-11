<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';

    protected $fillable = [
        'post_id', // Foreign key
        'user_id', // Foreign key
        'body', // required
        'status', // default 0
        'created_by', // Foreign key, user id
        'updated_by',// Foreign key, user id
    ];

    protected $casts = [
        'post_id' => 'integer',
        'user_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'status' => 'boolean',
    ];


    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
