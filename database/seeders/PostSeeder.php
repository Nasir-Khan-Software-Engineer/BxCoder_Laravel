<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::take(2)->get(); // creator/updater
        $categories = Category::take(5)->get(); // some categories

        for ($i = 1; $i <= 5; $i++) {
            $post = Post::create([
                'title' => "Post $i",
                'slug' => "post-$i",
                'keywords' => "post$i, laravel, php",
                'description' => "Description for Post $i",
                'image' => null, // or set image path/base64
                'body' => "This is the body content for Post $i",
                'project_url' => "https://example.com/project$i",
                'video_url' => "https://youtube.com/example/video$i",
                'code_url' => "https://github.com/example/code$i",
                'created_by' => $users->first()->id,
                'updated_by' => $users->last()->id,
                'is_active' => true,
            ]);

            // attach random categories (1-3 per post)
            $postCategories = $categories->random(rand(1,3))->pluck('id')->toArray();
            $post->categories()->sync($postCategories);
        }
    }
}
