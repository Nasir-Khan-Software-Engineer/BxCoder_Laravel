<?php
namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="API endpoints for managing categories"
 * )
 *
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Technology"),
 *     @OA\Property(property="slug", type="string", example="technology"),
 *     @OA\Property(property="image", type="string", nullable=true, example="base64_encoded_image_string"),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="keywords", type="string", example="tech, innovation, gadgets"),
 *     @OA\Property(property="description", type="string", example="All about technology"),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="updated_by", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="posts_count", type="integer", example=5),
 *     @OA\Property(property="projects_count", type="integer", example=3),
 *     @OA\Property(property="creator", type="object", ref="#/components/schemas/User"),
 *     @OA\Property(property="updater", type="object", ref="#/components/schemas/User")
 * )
 */
class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/category-list",
     *     summary="Get paginated list of categories",
     *     tags={"Categories"},
     *     @OA\Parameter(name="per_page", in="query", description="Number of items per page (default: 15, max: 100)", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="search", in="query", description="Search in name, slug, keywords, description", required=false, @OA\Schema(type="string", example="tech")),
     *     @OA\Parameter(name="status", in="query", description="Filter by status", required=false, @OA\Schema(type="boolean", example=true)),
     *     @OA\Parameter(name="sort_by", in="query", description="Sort by field (name, slug, created_at, updated_at)", required=false, @OA\Schema(type="string", example="name")),
     *     @OA\Parameter(name="sort_order", in="query", description="Sort order (asc or desc)", required=false, @OA\Schema(type="string", enum={"asc","desc"}, example="asc")),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Categories retrieved successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Category")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=150),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=15)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'per_page'   => 'nullable|integer|min:1|max:100',
                'page'       => 'nullable|integer|min:1',
                'search'     => 'nullable|string|max:200',
                'status'     => 'nullable|boolean',
                'sort_by'    => 'nullable|string|in:name,slug,created_at,updated_at',
                'sort_order' => 'nullable|string|in:asc,desc',
            ]);

            $perPage   = $validated['per_page'] ?? 15;
            $sortBy    = $validated['sort_by'] ?? 'created_at';
            $sortOrder = $validated['sort_order'] ?? 'desc';

            $query = Category::with(['creator', 'updater'])->withCount(['posts', 'projects']);

            if (! empty($validated['search'])) {
                $searchTerm = $validated['search'];
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('slug', 'like', "%{$searchTerm}%")
                        ->orWhere('keywords', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            if (isset($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            $query->orderBy($sortBy, $sortOrder);

            $categories = $query->paginate($perPage);

            Log::info('Categories list retrieved', [
                'total'         => $categories->total(),
                'page'          => $categories->currentPage(),
                'search'        => $validated['search'] ?? null,
                'status_filter' => $validated['status'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully.',
                'data'    => CategoryResource::collection($categories),
                'meta'    => [
                    'current_page' => $categories->currentPage(),
                    'last_page'    => $categories->lastPage(),
                    'per_page'     => $categories->perPage(),
                    'total'        => $categories->total(),
                    'from'         => $categories->firstItem(),
                    'to'           => $categories->lastItem(),
                ],
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('Validation failed for categories list', [
                'errors' => $e->errors(),
                'ip'     => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve categories list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/category-show/{id}",
     *     summary="Get a specific category by ID",
     *     tags={"Categories"},
     *     @OA\Parameter(name="id", in="path", description="Category ID", required=true, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category retrieved successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Category not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $category = Category::with(['creator', 'updater', 'posts', 'projects'])
                ->withCount(['posts', 'projects'])
                ->findOrFail($id);

            Log::info('Category retrieved successfully', [
                'id'   => $category->id,
                'name' => $category->name,
                'ip'   => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category retrieved successfully.',
                'data'    => new CategoryResource($category),
            ], 200);

        } catch (ModelNotFoundException $e) {
            Log::warning('Category not found', [
                'id' => $id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve category', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/category-create",
     *     summary="Create a new category",
     *     tags={"Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","slug","keywords","description"},
     *             @OA\Property(property="name", type="string", minLength=3, maxLength=200, example="Technology"),
     *             @OA\Property(property="slug", type="string", minLength=3, maxLength=300, example="technology"),
     *             @OA\Property(property="image", type="string", nullable=true, example="base64_encoded_image_string"),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="keywords", type="string", minLength=3, maxLength=1000, example="tech, innovation, gadgets"),
     *             @OA\Property(property="description", type="string", minLength=3, maxLength=1000, example="All about technology")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Category created successfully", @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true), @OA\Property(property="message", type="string", example="Category created successfully."), @OA\Property(property="data", ref="#/components/schemas/Category"))),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $userId = auth()->id() ?? 1;

            $validated = $request->validate([
                'name'        => 'required|string|min:3|max:200|unique:categories,name',
                'slug'        => 'required|string|min:3|max:300|unique:categories,slug',
                'image'       => 'nullable|string',
                'status'      => 'nullable|boolean',
                'keywords'    => 'required|string|min:3|max:1000',
                'description' => 'required|string|min:3|max:1000',
            ]);

            $validated['created_by'] = $userId;
            $validated['updated_by'] = $userId;
            $validated['status']     = $validated['status'] ?? true;

            $category = Category::create($validated);
            $category->load(['creator', 'updater']);

            DB::commit();

            Log::info('Category created successfully', [
                'id'         => $category->id,
                'name'       => $category->name,
                'created_by' => $userId,
                'ip'         => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'data'    => new CategoryResource($category),
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();

            Log::warning('Validation failed for category creation', [
                'errors' => $e->errors(),
                'ip'     => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create category', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create category. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/category-update/{id}",
     *     summary="Update an existing category",
     *     tags={"Categories"},
     *     @OA\Parameter(name="id", in="path", description="Category ID", required=true, @OA\Schema(type="integer", example=1)),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","slug","keywords","description"},
     *             @OA\Property(property="name", type="string", minLength=3, maxLength=200, example="Technology Updated"),
     *             @OA\Property(property="slug", type="string", minLength=3, maxLength=300, example="technology-updated"),
     *             @OA\Property(property="image", type="string", nullable=true, example="base64_encoded_image_string"),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="keywords", type="string", minLength=3, maxLength=1000, example="tech, gadgets, innovation"),
     *             @OA\Property(property="description", type="string", minLength=3, maxLength=1000, example="Updated description")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Category updated successfully", @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true), @OA\Property(property="message", type="string", example="Category updated successfully."), @OA\Property(property="data", ref="#/components/schemas/Category"))),
     *     @OA\Response(response=404, description="Category not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $category = Category::findOrFail($id);
            $userId   = auth()->id() ?? 1;

            $validated = $request->validate([
                'name' => "required|string|min:3|max:200|unique:categories,name,{$id}",
                'slug' => "required|string|min:3|max:300|unique:categories,slug,{$id}",
                'image'       => 'nullable|string',
                'status'      => 'nullable|boolean',
                'keywords'    => 'required|string|min:3|max:1000',
                'description' => 'required|string|min:3|max:1000',
            ]);

            $validated['updated_by'] = $userId;

            $category->update($validated);
            $category->load(['creator', 'updater']);

            DB::commit();

            Log::info('Category updated successfully', [
                'id'         => $category->id,
                'name'       => $category->name,
                'updated_by' => $userId,
                'ip'         => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'data'    => new CategoryResource($category),
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            Log::warning('Category not found for update', [
                'id' => $id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);

        } catch (ValidationException $e) {
            DB::rollBack();

            Log::warning('Validation failed for category update', [
                'id'     => $id,
                'errors' => $e->errors(),
                'ip'     => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update category', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update category. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/category-delete/{id}",
     *     summary="Delete a category",
     *     tags={"Categories"},
     *     @OA\Parameter(name="id", in="path", description="Category ID", required=true, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Category deleted successfully", @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true), @OA\Property(property="message", type="string", example="Category deleted successfully."))),
     *     @OA\Response(response=422, description="Business logic error", @OA\JsonContent(@OA\Property(property="success", type="boolean", example=false), @OA\Property(property="message", type="string", example="Cannot delete category with existing posts or projects."))),
     *     @OA\Response(response=404, description="Category not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $category = Category::withCount(['posts', 'projects'])->findOrFail($id);

            if ($category->posts_count > 0 || $category->projects_count > 0) {
                throw new \Exception('Cannot delete category with existing posts or projects.');
            }

            $categoryName = $category->name;
            $categorySlug = $category->slug;

            $category->delete();
            DB::commit();

            Log::info('Category deleted successfully', [
                'id'   => $id,
                'name' => $categoryName,
                'slug' => $categorySlug,
                'ip'   => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.',
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            Log::warning('Category not found for deletion', [
                'id' => $id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();

            if (str_contains($e->getMessage(), 'Cannot delete category')) {
                Log::warning('Business logic error during category deletion', [
                    'id'    => $id,
                    'error' => $e->getMessage(),
                    'ip'    => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            Log::error('Failed to delete category', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category. Please try again.',
            ], 500);
        }
    }
}
