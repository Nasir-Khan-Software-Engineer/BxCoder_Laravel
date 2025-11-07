<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create([
            'site_name' => 'My Blog & Source Code Project',
            'site_email' => 'info@example.com',
            'site_phone' => '+880123456789',
            'site_logo' => 'logo.png',
            'favicon' => 'favicon.ico',
            'meta_title' => 'My Blog & Source Code Project',
            'meta_keywords' => 'blog, laravel, source code, projects',
            'meta_description' => 'This is a blog and source code selling project.',
            'copyright' => 'Copyright &copy; 2023 My Blog & Source Code Project',
            'facebook' => 'https://www.facebook.com',
            'twitter' => 'https://www.twitter.com',
            'instagram' => 'https://www.instagram.com',
            'linkedin' => 'https://www.linkedin.com',
            'youtube' => 'https://www.youtube.com',
            'created_by' => 1,
        ]);
    }
}
