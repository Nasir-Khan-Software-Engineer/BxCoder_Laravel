<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use Illuminate\Support\Str;
use App\Models\Category;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            ['title' => 'Post One', 'slug' => 'post-one'],
            ['title' => 'Post Two', 'slug' => 'post-two'],
            ['title' => 'Post Three', 'slug' => 'post-three'],
            ['title' => 'Post Four', 'slug' => 'post-four'],
            ['title' => 'Post Five', 'slug' => 'post-five'],
        ];

        foreach ($posts as $postData) {
            $post = Post::create([
                'title' => $postData['title'],
                'slug' => $postData['slug'],
                'keywords' => $postData['slug'] . ',demo,laravel',
                'description' => "This is the {$postData['title']} description",
                'body' => "This is the body content of {$postData['title']}.",
                'project_url' => 'https://example.com/' . $postData['slug'],
                'created_by' => 1, // Ensure user id 1 exists
            ]);

            // Attach random categories (example: 2 categories each)
            $categoryIds = Category::inRandomOrder()->take(2)->pluck('id')->toArray();
            $post->categories()->attach($categoryIds);
        }
    }
}

