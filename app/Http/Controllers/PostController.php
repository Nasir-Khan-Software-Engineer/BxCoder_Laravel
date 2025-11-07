<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        try {
            $posts = Post::with('creator', 'updater')->latest()->get();
            // format created at time 
            foreach ($posts as $post) {
                $post->formatedCreatedAt = formatDateAndTime($post->created_at);
            }
            return view('posts.index', compact('posts'));
        } catch (\Throwable $e) {
            Log::error('Post Index Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to fetch posts.');
        }
    }

    public function create()
    {
        try {
            return view('posts.create');
        } catch (\Throwable $e) {
            Log::error('Post Create Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to load create form'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title'        => 'required|unique:posts,title',
                'slug'         => 'nullable|unique:posts,slug',
                'keywords'     => 'nullable|string',
                'description'  => 'nullable|string',
                'image'        => 'nullable|string',
                'body'         => 'nullable|string',
                'project_url'  => 'nullable|url',
                'categories'   => 'required|array',       // NEW
                'categories.*' => 'exists:categories,id', // NEW
            ]);

            $post = Post::create([
                'title'       => $request->title,
                'slug'        => $request->slug ? Str::slug($request->slug) : Str::slug($request->title),
                'keywords'    => $request->keywords,
                'description' => $request->description,
                'image'       => $request->image,
                'body'        => $request->body,
                'project_url' => $request->project_url,
                'created_by'  => Auth::id(),
            ]);

            // Attach categories
            $post->categories()->attach($request->categories);

            return response()->json(['status' => 'success', 'message' => 'Post created successfully', 'post' => $post]);

        } catch (\Throwable $e) {
            Log::error('Post Store Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to create post'], 500);
        }
    }

    public function edit(Post $post)
    {
        try {
            return view('posts.edit', compact('post'));
        } catch (\Throwable $e) {
            Log::error('Post Edit Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to load edit form'], 500);
        }
    }

    public function update(Request $request, Post $post)
    {
        try {
            // update()
            $request->validate([
                'title'        => 'required|unique:posts,title,' . $post->id,
                'slug'         => 'nullable|unique:posts,slug,' . $post->id,
                'keywords'     => 'nullable|string',
                'description'  => 'nullable|string',
                'image'        => 'nullable|string',
                'body'         => 'nullable|string',
                'project_url'  => 'nullable|url',
                'categories'   => 'required|array',       // NEW
                'categories.*' => 'exists:categories,id', // NEW
            ]);

            $post->update([
                'title'       => $request->title,
                'slug'        => $request->slug ? Str::slug($request->slug) : Str::slug($request->title),
                'keywords'    => $request->keywords,
                'description' => $request->description,
                'image'       => $request->image,
                'body'        => $request->body,
                'project_url' => $request->project_url,
                'updated_by'  => Auth::id(),
            ]);

            // Sync categories (overwrite old ones)
            $post->categories()->sync($request->categories);

            return response()->json(['status' => 'success', 'message' => 'Post updated successfully', 'post' => $post]);

        } catch (\Throwable $e) {
            Log::error('Post Update Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to update post'], 500);
        }
    }

    public function destroy(Post $post)
    {
        try {
            $post->delete();
            return response()->json(['status' => 'success', 'message' => 'Post deleted successfully']);
        } catch (\Throwable $e) {
            Log::error('Post Delete Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to delete post'], 500);
        }
    }
}
