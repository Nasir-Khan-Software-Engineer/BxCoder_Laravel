<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_name',
        'site_email',
        'site_phone',
        'site_logo',
        'favicon',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'copyright',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'youtube',
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
}
