<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        try {
            $projects = Project::with('categories', 'creator', 'updater')->latest()->get();
            foreach ($projects as $project) {
                $project->formatedCreatedAt = formatDateAndTime($project->created_at);
            }
            return view('admin.projects.index', compact('projects'));
        } catch (Exception $e) {
            Log::error('Project Index Error: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
        }
    }

    public function create()
    {
        try {
            $categories = Category::all();
            return view('admin.projects.create', compact('categories'));
        } catch (\Throwable $e) {
            Log::error('Project Create Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status'=>'error','message'=>'Failed to load form'],500);
        }
    }

    public function store(Request $request)
    {
        // Let Laravel handle validation automatically
        $validated = $request->validate([
            'title' => 'required|unique:projects,title',
            'keywords' => 'nullable|string',
            'short_description' => 'nullable|string',
            'details' => 'nullable|string',
            'source_code_link' => 'nullable|url',
            'video_link' => 'nullable|url',
            'documentation_link' => 'nullable|url',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id'
        ]);

        try {
            $project = Project::create(array_merge(
                $request->only([
                    'title','keywords','short_description','details',
                    'source_code_link','video_link','documentation_link'
                ]),
                ['created_by' => Auth::id()]
            ));

            $project->categories()->attach($request->categories);

            Log::info('Project Created: ID '.$project->id.' by user '.Auth::id());

            return redirect()->route('admin.projects.index')
                ->with('success', 'Project created successfully.');

        } catch (\Throwable $e) {
            Log::error('Project Store Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['error' => 'Failed to create project.']);
        }
    }


    public function edit(Project $project)
    {
        try {
            $categories = Category::all();
            return view('admin.projects.edit', compact('project','categories'));
        } catch (\Throwable $e) {
            Log::error('Project Edit Error: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            return response()->json(['status'=>'error','message'=>'Failed to load edit form'],500);
        }
    }

    public function update(Request $request, Project $project)
    {
        // Validate first (Laravel handles redirect on failure automatically)
        $validated = $request->validate([
            'title' => 'required|unique:projects,title,' . $project->id,
            'keywords' => 'nullable|string',
            'short_description' => 'nullable|string',
            'details' => 'nullable|string',
            'source_code_link' => 'nullable|url',
            'video_link' => 'nullable|url',
            'documentation_link' => 'nullable|url',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id'
        ]);

        try {
            // Update project data
            $project->update(array_merge(
                $request->only([
                    'title','keywords','short_description','details',
                    'source_code_link','video_link','documentation_link'
                ]),
                ['updated_by' => Auth::id()]
            ));

            // Sync categories
            $project->categories()->sync($request->categories);

            // Log success
            Log::info('Project Updated: ID '.$project->id.' by user '.Auth::id());

            // Redirect back with success
            return redirect()->route('admin.projects.index')
                ->with('success', 'Project updated successfully.');

        } catch (\Throwable $e) {
            Log::error('Project Update Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['error' => 'Failed to update project.']);
        }
    }


    public function destroy(Project $project)
    {
        try {
            $project->delete();
            return response()->json(['status'=>'success','message'=>'Project deleted successfully']);
        } catch (Exception $e) {
            Log::error('Project Delete Error: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
        }
    }
}
