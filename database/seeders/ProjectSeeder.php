<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Category;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'title' => 'Project One',
                'keywords' => 'laravel, project, demo',
                'short_description' => 'Short description of Project One',
                'details' => 'Detailed description of Project One',
                'source_code_link' => 'https://github.com/example/project-one',
                'video_link' => 'https://youtube.com/example/project-one',
                'documentation_link' => 'https://docs.example.com/project-one',
            ],
            [
                'title' => 'Project Two',
                'keywords' => 'laravel, project, demo',
                'short_description' => 'Short description of Project Two',
                'details' => 'Detailed description of Project Two',
                'source_code_link' => 'https://github.com/example/project-two',
                'video_link' => 'https://youtube.com/example/project-two',
                'documentation_link' => 'https://docs.example.com/project-two',
            ],
            // Add more projects if needed
        ];

        foreach ($projects as $projData) {
            $project = Project::create(array_merge($projData, ['created_by' => 1]));

            // Attach 1-2 random categories
            $categoryIds = Category::inRandomOrder()->take(2)->pluck('id')->toArray();
            $project->categories()->attach($categoryIds);
        }
    }
}
