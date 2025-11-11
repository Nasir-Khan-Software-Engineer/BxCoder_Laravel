<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Category;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::take(2)->get(); // creator/updater
        $categories = Category::take(5)->get(); // some categories

        for ($i = 1; $i <= 5; $i++) {
            $project = Project::create([
                'title' => "Project $i",
                'keywords' => "project$i, laravel, php",
                'short_description' => "Short description for Project $i",
                'details' => "Detailed description and content of Project $i",
                'source_code_link' => "https://github.com/example/project$i",
                'video_link' => "https://youtube.com/example/project$i",
                'documentation_link' => "https://docs.example.com/project$i",
                'created_by' => $users->first()->id,
                'updated_by' => $users->last()->id,
            ]);

            // attach random categories (1-3 categories per project)
            $projectCategories = $categories->random(rand(1,3))->pluck('id')->toArray();
            $project->categories()->sync($projectCategories);
        }
    }
}
