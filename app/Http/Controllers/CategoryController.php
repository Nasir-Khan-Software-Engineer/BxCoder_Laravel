<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categories = Category::all();
            return view('admin.category.index', compact('categories'));
        } catch (\Throwable $e) {
            Log::error('Category Index Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Something went wrong while fetching categories.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('admin.category.create');
        } catch (\Throwable $e) {
            Log::error('Category Create Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to load create form'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:categories,name',
                'slug' => 'nullable|unique:categories,slug',
                'keywords' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            $category = Category::create([
                'name' => $request->name,
                'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->name),
                'keywords' => $request->keywords,
                'description' => $request->description,
                'created_by' => Auth::id(),
            ]);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Post created successfully.');
        } catch (\Throwable $e) {
            Log::error('Category Store Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to create category'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        try {
            return view('admin.category.edit', compact('category'));
        } catch (\Throwable $e) {
            Log::error('Category Edit Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to load edit form'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        try {
            $request->validate([
                'name' => 'required|unique:categories,name,' . $category->id,
                'slug' => 'nullable|unique:categories,slug,' . $category->id,
                'keywords' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            $category->update([
                'name' => $request->name,
                'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->name),
                'keywords' => $request->keywords,
                'description' => $request->description,
                'updated_by' => Auth::id(),
            ]);
            return redirect()->route('admin.categories.index')
                ->with('success', 'Post created successfully.');
            
        } catch (\Throwable $e) {
            Log::error('Category Update Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to update category'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            $category->delete();
            return response()->json(['status' => 'success', 'message' => 'Category deleted successfully']);
        } catch (\Throwable $e) {
            Log::error('Category Delete Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to delete category'], 500);
        }
    }
}
