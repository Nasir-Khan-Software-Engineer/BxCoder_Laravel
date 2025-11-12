<?php
namespace App\Http\Controllers;

use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Brands",
 *     description="API endpoints for managing brands"
 * )
 *
 * @OA\Schema(
 *     schema="Brand",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Nike"),
 *     @OA\Property(property="slug", type="string", example="nike"),
 *     @OA\Property(property="image", type="string", nullable=true, example="base64_encoded_image_string"),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="keywords", type="string", example="shoes, sportswear, apparel"),
 *     @OA\Property(property="description", type="string", example="Leading sportswear brand"),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="updated_by", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="products_count", type="integer", example=5),
 *     @OA\Property(property="creator", type="object", ref="#/components/schemas/User"),
 *     @OA\Property(property="updater", type="object", ref="#/components/schemas/User")
 * )
 */
class BrandController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/brand-list",
     *     summary="Get paginated list of brands",
     *     tags={"Brands"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page (default: 15, max: 100)",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in name, slug, keywords, description",
     *         required=false,
     *         @OA\Schema(type="string", example="nike")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort by field (name, slug, created_at, updated_at)",
     *         required=false,
     *         @OA\Schema(type="string", example="name")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="asc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Brands retrieved successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Brand")),
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
            // Validate query parameters
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

            // Build query with eager loading and search functionality
            $query = Brand::with(['creator', 'updater'])->withCount('products');

            // Search across multiple fields
            if (! empty($validated['search'])) {
                $searchTerm = $validated['search'];
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('slug', 'like', "%{$searchTerm}%")
                        ->orWhere('keywords', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            // Filter by status
            if (isset($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $brands = $query->paginate($perPage);

            // Log successful retrieval
            Log::info('Brands list retrieved', [
                'total'         => $brands->total(),
                'page'          => $brands->currentPage(),
                'search'        => $validated['search'] ?? null,
                'status_filter' => $validated['status'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Brands retrieved successfully.',
                'data'    => BrandResource::collection($brands),
                'meta'    => [
                    'current_page' => $brands->currentPage(),
                    'last_page'    => $brands->lastPage(),
                    'per_page'     => $brands->perPage(),
                    'total'        => $brands->total(),
                    'from'         => $brands->firstItem(),
                    'to'           => $brands->lastItem(),
                ],
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('Validation failed for brands list', [
                'errors' => $e->errors(),
                'ip'     => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve brands list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve brands. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/brand-show/{id}",
     *     summary="Get a specific brand by ID",
     *     tags={"Brands"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Brand ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Brand retrieved successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Brand")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Brand not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            // Find brand with relationships or fail
            $brand = Brand::with(['creator', 'updater', 'products'])
                ->withCount('products')
                ->findOrFail($id);

            // Log successful retrieval
            Log::info('Brand retrieved successfully', [
                'id'              => $brand->id,
                'name'            => $brand->name,
                'requested_by_ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Brand retrieved successfully.',
                'data'    => new BrandResource($brand),
            ], 200);

        } catch (ModelNotFoundException $e) {
            Log::warning('Brand not found for show', [
                'id' => $id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Brand not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve brand', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve brand. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/brand-create",
     *     summary="Create a new brand",
     *     tags={"Brands"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "slug", "keywords", "description"},
     *             @OA\Property(property="name", type="string", minLength=3, maxLength=200, example="Nike"),
     *             @OA\Property(property="slug", type="string", minLength=3, maxLength=300, example="nike"),
     *             @OA\Property(property="image", type="string", nullable=true, example="base64_encoded_image_string"),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="keywords", type="string", minLength=3, maxLength=1000, example="shoes, sportswear, apparel"),
     *             @OA\Property(property="description", type="string", minLength=3, maxLength=1000, example="Leading sportswear brand")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Brand created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Brand created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Brand")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
                                         // Get authenticated user ID (assuming you have authentication)
            $userId = auth()->id() ?? 1; // Fallback for development

            // Validate input
            $validated = $request->validate([
                'name'        => 'required|string|min:3|max:200|unique:brands,name',
                'slug'        => 'required|string|min:3|max:300|unique:brands,slug',
                'image'       => 'nullable|string', // Base64 string
                'status'      => 'nullable|boolean',
                'keywords'    => 'required|string|min:3|max:1000',
                'description' => 'required|string|min:3|max:1000',
            ]);

            // Add user tracking
            $validated['created_by'] = $userId;
            $validated['updated_by'] = $userId;
            $validated['status']     = $validated['status'] ?? true;

            // Create brand
            $brand = Brand::create($validated);

            // Load relationships for response
            $brand->load(['creator', 'updater']);

            DB::commit();

            // Log successful creation
            Log::info('Brand created successfully', [
                'id'            => $brand->id,
                'name'          => $brand->name,
                'created_by'    => $userId,
                'created_by_ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully.',
                'data'    => new BrandResource($brand),
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();

            Log::warning('Validation failed for brand creation', [
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

            Log::error('Failed to create brand', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create brand. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/brand-update/{id}",
     *     summary="Update an existing brand",
     *     tags={"Brands"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Brand ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "slug", "keywords", "description"},
     *             @OA\Property(property="name", type="string", minLength=3, maxLength=200, example="Nike Updated"),
     *             @OA\Property(property="slug", type="string", minLength=3, maxLength=300, example="nike-updated"),
     *             @OA\Property(property="image", type="string", nullable=true, example="base64_encoded_image_string"),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="keywords", type="string", minLength=3, maxLength=1000, example="shoes, sportswear, apparel, updated"),
     *             @OA\Property(property="description", type="string", minLength=3, maxLength=1000, example="Leading sportswear brand - updated description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Brand updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Brand")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Brand not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Find brand or fail
            $brand = Brand::findOrFail($id);

                                         // Get authenticated user ID
            $userId = auth()->id() ?? 1; // Fallback for development

            // Validate input with unique rules excluding current record
            $validated = $request->validate([
                'name' => "required|string|min:3|max:200|unique:brands,name,{$id}",
                'slug' => "required|string|min:3|max:300|unique:brands,slug,{$id}",
                'image'       => 'nullable|string',
                'status'      => 'nullable|boolean',
                'keywords'    => 'required|string|min:3|max:1000',
                'description' => 'required|string|min:3|max:1000',
            ]);

            // Add updated_by tracking
            $validated['updated_by'] = $userId;

            // Update brand
            $brand->update($validated);

            // Load relationships for response
            $brand->load(['creator', 'updater']);

            DB::commit();

            // Log successful update
            Log::info('Brand updated successfully', [
                'id'            => $brand->id,
                'name'          => $brand->name,
                'updated_by'    => $userId,
                'updated_by_ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Brand updated successfully.',
                'data'    => new BrandResource($brand),
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            Log::warning('Brand not found for update', [
                'id' => $id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Brand not found.',
            ], 404);

        } catch (ValidationException $e) {
            DB::rollBack();

            Log::warning('Validation failed for brand update', [
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

            Log::error('Failed to update brand', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update brand. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/brand-delete/{id}",
     *     summary="Delete a brand",
     *     tags={"Brands"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Brand ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Brand deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Business logic error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cannot delete brand with existing products.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Brand not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Find brand with products count or fail
            $brand = Brand::withCount('products')->findOrFail($id);

            // Check if brand has products
            if ($brand->products_count > 0) {
                throw new \Exception('Cannot delete brand with existing products.');
            }

            // Store info for logging before deletion
            $brandName = $brand->name;
            $brandSlug = $brand->slug;

            // Delete brand
            $brand->delete();

            DB::commit();

            // Log successful deletion
            Log::info('Brand deleted successfully', [
                'id'            => $id,
                'name'          => $brandName,
                'slug'          => $brandSlug,
                'deleted_by_ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Brand deleted successfully.',
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            Log::warning('Brand not found for deletion', [
                'id' => $id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Brand not found.',
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();

            // Check if it's a business logic error
            if (str_contains($e->getMessage(), 'Cannot delete brand')) {
                Log::warning('Business logic error during brand deletion', [
                    'id'    => $id,
                    'error' => $e->getMessage(),
                    'ip'    => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            Log::error('Failed to delete brand', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete brand. Please try again.',
            ], 500);
        }
    }
}
