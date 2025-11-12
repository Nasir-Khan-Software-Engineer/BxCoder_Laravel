<?php

namespace App\Http\Controllers;

use App\Http\Resources\SiteFeatureResource;
use App\Models\SiteFeature;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *     name="Site Features",
 *     description="API endpoints for managing site features"
 * )
 *
 * @OA\Schema(
 *     schema="SiteFeature",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Dark Mode"),
 *     @OA\Property(property="is_default", type="boolean", example=false),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="updated_by", type="integer", nullable=true, example=2),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T12:00:00Z")
 * )
 */
class SiteFeatureController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/site-feature-list",
     *     summary="Get paginated list of site features",
     *     tags={"Site Features"},
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string", example="Dark")),
     *     @OA\Response(response=200, description="List retrieved successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'per_page' => 'nullable|integer|min:1|max:100',
                'search' => 'nullable|string|max:255',
            ]);

            $perPage = $validated['per_page'] ?? 15;

            $query = SiteFeature::query()->with(['creator', 'updater']);

            if (!empty($validated['search'])) {
                $query->where('name', 'like', "%{$validated['search']}%");
            }

            $features = $query->orderByDesc('created_at')->paginate($perPage);

            Log::info('Site features retrieved', ['count' => $features->total()]);

            return response()->json([
                'success' => true,
                'message' => 'Site features retrieved successfully.',
                'data' => SiteFeatureResource::collection($features),
                'meta' => [
                    'current_page' => $features->currentPage(),
                    'last_page' => $features->lastPage(),
                    'per_page' => $features->perPage(),
                    'total' => $features->total(),
                    'from' => $features->firstItem(),
                    'to' => $features->lastItem(),
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve site features', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve site features.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/site-feature-show/{id}",
     *     summary="Get a single site feature by ID",
     *     tags={"Site Features"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Feature retrieved successfully"),
     *     @OA\Response(response=404, description="Feature not found")
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $feature = SiteFeature::with(['creator', 'updater'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Feature retrieved successfully.',
                'data' => new SiteFeatureResource($feature),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Feature not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve feature', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve feature.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/site-feature-create",
     *     summary="Create a new site feature",
     *     tags={"Site Features"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name"},
     *         @OA\Property(property="name", type="string", example="Dark Mode"),
     *         @OA\Property(property="is_default", type="boolean", example=false),
     *         @OA\Property(property="is_active", type="boolean", example=true)
     *     )),
     *     @OA\Response(response=201, description="Feature created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $userId = auth()->id() ?? 1;

            $validated = $request->validate([
                'name' => 'required|string|min:3|max:255|unique:site_features,name',
                'is_default' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['created_by'] = $userId;
            $validated['updated_by'] = $userId;
            $validated['is_default'] = $validated['is_default'] ?? false;
            $validated['is_active'] = $validated['is_active'] ?? true;

            $feature = SiteFeature::create($validated);

            DB::commit();

            Log::info('Site feature created', ['id' => $feature->id, 'created_by' => $userId]);

            return response()->json([
                'success' => true,
                'message' => 'Feature created successfully.',
                'data' => new SiteFeatureResource($feature),
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
            Log::error('Failed to create feature', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create feature.',
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/site-feature-update/{id}",
     *     summary="Update an existing site feature",
     *     tags={"Site Features"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name"},
     *         @OA\Property(property="name", type="string", example="Dark Mode"),
     *         @OA\Property(property="is_default", type="boolean", example=false),
     *         @OA\Property(property="is_active", type="boolean", example=true)
     *     )),
     *     @OA\Response(response=200, description="Feature updated successfully"),
     *     @OA\Response(response=404, description="Feature not found")
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $feature = SiteFeature::findOrFail($id);
            $userId = auth()->id() ?? 1;

            $validated = $request->validate([
                'name' => "required|string|min:3|max:255|unique:site_features,name,{$id}",
                'is_default' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['updated_by'] = $userId;

            $feature->update($validated);

            DB::commit();

            Log::info('Feature updated', ['id' => $feature->id, 'updated_by' => $userId]);

            return response()->json([
                'success' => true,
                'message' => 'Feature updated successfully.',
                'data' => new SiteFeatureResource($feature),
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Feature not found.',
            ], 404);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update feature', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update feature.',
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/site-feature-delete/{id}",
     *     summary="Delete a site feature",
     *     tags={"Site Features"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Feature deleted successfully"),
     *     @OA\Response(response=404, description="Feature not found")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $feature = SiteFeature::findOrFail($id);
            $feature->delete();

            DB::commit();

            Log::info('Feature deleted', ['id' => $id, 'deleted_by' => auth()->id() ?? 1]);

            return response()->json([
                'success' => true,
                'message' => 'Feature deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Feature not found.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete feature', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete feature.',
            ], 500);
        }
    }
}
