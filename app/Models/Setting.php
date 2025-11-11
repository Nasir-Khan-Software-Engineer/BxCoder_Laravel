<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',             // required, string, min 3, max 1000
        'email',            // required, string, min 3, max 200, email
        'phone',            // required, string, min 11, max 11
        'address',          // required, string, min 3, max 1000

        'logo',             // required, base64 string (or better: file path)
        'favicon',          // required, base64 string (or better: file path)

        'meta_title',       // required, string, min 3, max 200
        'meta_keywords',    // required, string, min 3, max 1000
        'meta_description', // required, string, min 3, max 1000

        'copyright',        // required, string, min 3, max 200

        'facebook',         // nullable, url
        'twitter',          // nullable, url
        'instagram',        // nullable, url
        'linkedin',         // nullable, url
        'youtube',          // nullable, url
        'tiktok',           // ✅ renamed from 'ticktok', nullable, url

        'product_prefix',   // required, string, min 3, max 10
        'order_prefix',     // required, string, min 3, max 10
        'invoice_prefix',   // required, string, min 3, max 10

        'created_by',       // required, foreign key (user_id)
        'updated_by',       // nullable, foreign key (user_id)
    ];

    protected $casts = [
        'name' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'address' => 'string',

        'logo' => 'string',
        'favicon' => 'string',

        'meta_title' => 'string',
        'meta_keywords' => 'string',
        'meta_description' => 'string',

        'copyright' => 'string',

        'facebook' => 'string',
        'twitter' => 'string',
        'instagram' => 'string',
        'linkedin' => 'string',
        'youtube' => 'string',
        'tiktok' => 'string',

        'product_prefix' => 'string',
        'order_prefix' => 'string',
        'invoice_prefix' => 'string',

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