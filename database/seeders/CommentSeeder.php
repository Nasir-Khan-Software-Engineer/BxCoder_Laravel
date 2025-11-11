<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::take(3)->get(); // assuming 3 users exist
        $posts = Post::take(5)->get(); // 5 posts

        foreach ($posts as $post) {
            foreach ($users as $user) {
                Comment::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                    'body' => "This is a comment by {$user->name} on {$post->title}.",
                    'status' => true,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }
        }
    }
}
