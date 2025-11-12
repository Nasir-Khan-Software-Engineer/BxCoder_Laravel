<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="API Endpoints for managing products"
 * )
 *
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Premium Laptop"),
 *     @OA\Property(property="slug", type="string", example="premium-laptop"),
 *     @OA\Property(property="code", type="string", example="PRD-001"),
 *     @OA\Property(property="details", type="string", example="High-end gaming laptop"),
 *     @OA\Property(property="keywords", type="string", example="laptop, gaming, tech"),
 *     @OA\Property(property="short_description", type="string", example="Powerful laptop for gaming and design"),
 *     @OA\Property(property="brand_id", type="integer", example=2),
 *     @OA\Property(property="unit_id", type="integer", example=1),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="updated_by", type="integer", nullable=true, example=2),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/product-list",
     *     summary="List all products with pagination, search, and sorting",
     *     tags={"Products"},
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", enum={"asc","desc"})),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=15)),
     *     @OA\Response(response=200, description="Products retrieved successfully"),
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

            $query = Product::with([
                'brand',  'categories', 'stocks', 'images', 'reviews', 'creator', 'updater'
            ]);

            if (!empty($validated['search'])) {
                $query->where(function ($q) use ($validated) {
                    $q->where('name', 'like', "%{$validated['search']}%")
                      ->orWhere('code', 'like', "%{$validated['search']}%")
                      ->orWhere('keywords', 'like', "%{$validated['search']}%");
                });
            }

            $sort = $validated['sort'] ?? 'desc';
            $perPage = $validated['per_page'] ?? 15;

            $products = $query->orderBy('created_at', $sort)->paginate($perPage);

            Log::info('Products retrieved', ['count' => $products->total()]);

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully.',
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve products', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to retrieve products.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/product-show/{id}",
     *     summary="Show a specific product by ID",
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Product retrieved successfully"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $product = Product::with([
                'brand', 'categories', 'stocks', 'images', 'reviews', 'creator', 'updater'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Product retrieved successfully.',
                'data' => $product,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve product', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to retrieve product.'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/product-create",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","slug","code","details","keywords","short_description","brand_id","unit_id","created_by"},
     *         @OA\Property(property="name", type="string", example="New Product"),
     *         @OA\Property(property="slug", type="string", example="new-product"),
     *         @OA\Property(property="code", type="string", example="PRD-002"),
     *         @OA\Property(property="details", type="string", example="Detailed product info"),
     *         @OA\Property(property="keywords", type="string", example="product, example"),
     *         @OA\Property(property="short_description", type="string", example="Short summary"),
     *         @OA\Property(property="brand_id", type="integer", example=1),
     *         @OA\Property(property="unit_id", type="integer", example=1),
     *         @OA\Property(property="created_by", type="integer", example=1),
     *         @OA\Property(property="status", type="string", example="pending"),
     *         @OA\Property(property="is_active", type="boolean", example=true)
     *     )),
     *     @OA\Response(response=201, description="Product created successfully"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'name' => 'required|string|min:3|unique:products,name',
                'slug' => 'required|string|min:3|unique:products,slug',
                'code' => 'required|string|min:3|unique:products,code',
                'details' => 'required|string|min:3',
                'keywords' => 'required|string|min:3',
                'short_description' => 'required|string|min:3',
                'brand_id' => 'required|integer|exists:brands,id',
                'unit_id' => 'required|integer|exists:units,id',
                'created_by' => 'required|integer|exists:users,id',
                'status' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['status'] = $validated['status'] ?? 'pending';
            $validated['is_active'] = $validated['is_active'] ?? false;

            $product = Product::create($validated);

            DB::commit();

            Log::info('Product created', ['id' => $product->id]);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'data' => $product,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create product', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to create product.'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/product-update/{id}",
     *     summary="Update an existing product",
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Product")),
     *     @OA\Response(response=200, description="Product updated successfully"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'name' => "required|string|min:3|unique:products,name,{$id}",
                'slug' => "required|string|min:3|unique:products,slug,{$id}",
                'code' => "required|string|min:3|unique:products,code,{$id}",
                'details' => 'required|string|min:3',
                'keywords' => 'required|string|min:3',
                'short_description' => 'required|string|min:3',
                'brand_id' => 'required|integer|exists:brands,id',
                'unit_id' => 'required|integer|exists:units,id',
                'updated_by' => 'nullable|integer|exists:users,id',
                'status' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            $product->update($validated);

            DB::commit();

            Log::info('Product updated', ['id' => $product->id]);

            return response()->json(['success' => true, 'message' => 'Product updated successfully.', 'data' => $product]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update product', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update product.'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/product-delete/{id}",
     *     summary="Delete a product by ID",
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Product deleted successfully"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            DB::commit();

            Log::info('Product deleted', ['id' => $id]);

            return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete product', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to delete product.'], 500);
        }
    }
}
