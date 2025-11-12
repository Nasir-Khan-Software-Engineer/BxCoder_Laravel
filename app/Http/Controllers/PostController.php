<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *     name="Posts",
 *     description="API Endpoints for managing blog posts"
 * )
 *
 * @OA\Schema(
 *     schema="Post",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Understanding Laravel 12"),
 *     @OA\Property(property="slug", type="string", example="understanding-laravel-12"),
 *     @OA\Property(property="keywords", type="string", example="Laravel, PHP, API"),
 *     @OA\Property(property="description", type="string", example="Comprehensive Laravel 12 guide"),
 *     @OA\Property(property="image", type="string", nullable=true, example="base64string..."),
 *     @OA\Property(property="body", type="string", example="<p>Full article body...</p>"),
 *     @OA\Property(property="project_url", type="string", nullable=true, example="https://example.com/project"),
 *     @OA\Property(property="video_url", type="string", nullable=true, example="https://youtube.com/example"),
 *     @OA\Property(property="code_url", type="string", nullable=true, example="https://github.com/example"),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="updated_by", type="integer", nullable=true, example=2),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-12T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-12T10:10:00Z")
 * )
 */
class PostController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/post-list",
     *     summary="Get paginated list of posts",
     *     tags={"Posts"},
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string"), description="Search by title or keywords"),
     *     @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", enum={"asc","desc"}), description="Sort order"),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=15)),
     *     @OA\Response(response=200, description="Posts retrieved successfully"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'search' => 'nullable|string|max:255',
                'sort' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $query = Post::with(['creator', 'updater', 'categories', 'comments']);

            if (!empty($validated['search'])) {
                $query->where(function ($q) use ($validated) {
                    $q->where('title', 'like', "%{$validated['search']}%")
                      ->orWhere('keywords', 'like', "%{$validated['search']}%");
                });
            }

            $sort = $validated['sort'] ?? 'desc';
            $perPage = $validated['per_page'] ?? 15;

            $posts = $query->orderBy('created_at', $sort)->paginate($perPage);

            Log::info('Posts retrieved', ['count' => $posts->total()]);

            return response()->json([
                'success' => true,
                'message' => 'Posts retrieved successfully.',
                'data' => PostResource::collection($posts),
                'meta' => [
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                    'from' => $posts->firstItem(),
                    'to' => $posts->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve posts', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve posts.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/post-show/{id}",
     *     summary="Get a single post by ID",
     *     tags={"Posts"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Post retrieved successfully"),
     *     @OA\Response(response=404, description="Post not found")
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $post = Post::with(['creator', 'updater', 'categories', 'comments'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Post retrieved successfully.',
                'data' => new PostResource($post),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve post', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve post.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/post-create",
     *     summary="Create a new post",
     *     tags={"Posts"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"title","slug","keywords","description","body","created_by"},
     *         @OA\Property(property="title", type="string", example="New Laravel Tutorial"),
     *         @OA\Property(property="slug", type="string", example="new-laravel-tutorial"),
     *         @OA\Property(property="keywords", type="string", example="Laravel, PHP"),
     *         @OA\Property(property="description", type="string", example="Learn Laravel step by step"),
     *         @OA\Property(property="body", type="string", example="<p>Full content here</p>"),
     *         @OA\Property(property="is_active", type="boolean", example=true)
     *     )),
     *     @OA\Response(response=201, description="Post created successfully"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'title' => 'required|string|min:3|max:1000|unique:posts,title',
                'slug' => 'required|string|min:3|max:1000|unique:posts,slug',
                'keywords' => 'required|string|min:3|max:1000',
                'description' => 'required|string|min:3|max:1000',
                'image' => 'nullable|string',
                'body' => 'required|string|min:3',
                'project_url' => 'nullable|url',
                'video_url' => 'nullable|url',
                'code_url' => 'nullable|url',
                'created_by' => 'required|integer',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['is_active'] = $validated['is_active'] ?? false;

            $post = Post::create($validated);

            DB::commit();

            Log::info('Post created', ['id' => $post->id, 'created_by' => $post->created_by]);

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully.',
                'data' => new PostResource($post),
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create post', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create post.',
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/post-update/{id}",
     *     summary="Update an existing post",
     *     tags={"Posts"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="slug", type="string"),
     *         @OA\Property(property="keywords", type="string"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="body", type="string"),
     *         @OA\Property(property="is_active", type="boolean")
     *     )),
     *     @OA\Response(response=200, description="Post updated successfully"),
     *     @OA\Response(response=404, description="Post not found")
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $post = Post::findOrFail($id);

            $validated = $request->validate([
                'title' => "required|string|min:3|max:1000|unique:posts,title,{$id}",
                'slug' => "required|string|min:3|max:1000|unique:posts,slug,{$id}",
                'keywords' => 'required|string|min:3|max:1000',
                'description' => 'required|string|min:3|max:1000',
                'image' => 'nullable|string',
                'body' => 'required|string|min:3',
                'project_url' => 'nullable|url',
                'video_url' => 'nullable|url',
                'code_url' => 'nullable|url',
                'updated_by' => 'nullable|integer',
                'is_active' => 'nullable|boolean',
            ]);

            $post->update($validated);

            DB::commit();

            Log::info('Post updated', ['id' => $post->id, 'updated_by' => $post->updated_by]);

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully.',
                'data' => new PostResource($post),
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Post not found.'], 404);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update post', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update post.'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/post-delete/{id}",
     *     summary="Delete a post by ID",
     *     tags={"Posts"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Post deleted successfully"),
     *     @OA\Response(response=404, description="Post not found")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $post = Post::findOrFail($id);
            $post->delete();

            DB::commit();

            Log::info('Post deleted', ['id' => $id, 'deleted_by' => auth()->id() ?? 'system']);

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Post not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete post', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to delete post.'], 500);
        }
    }
}
