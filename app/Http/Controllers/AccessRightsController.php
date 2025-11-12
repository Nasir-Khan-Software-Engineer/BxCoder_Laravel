<?php
namespace App\Http\Controllers;

use App\Http\Resources\AccessRightsResource;
use App\Models\AccessRights;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Access Rights",
 *     description="API endpoints for managing access rights"
 * )
 *
 * @OA\Schema(
 *     schema="AccessRights",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="route_name", type="string", example="api.admin.users.index"),
 *     @OA\Property(property="short_id", type="string", example="users_list"),
 *     @OA\Property(property="short_description", type="string", example="View list of users"),
 *     @OA\Property(property="details", type="string", nullable=true, example="This permission allows viewing all users in the system"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T12:00:00.000000Z")
 * )
 */
class AccessRightsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/access-right-list",
     *     summary="Get paginated list of access rights",
     *     tags={"Access Rights"},
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
     *         description="Search in route_name, short_id, short_description",
     *         required=false,
     *         @OA\Schema(type="string", example="admin")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort by field (route_name, short_id, created_at)",
     *         required=false,
     *         @OA\Schema(type="string", example="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Access rights retrieved successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AccessRights")),
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
                'sort_by'    => 'nullable|string|in:route_name,short_id,created_at,updated_at',
                'sort_order' => 'nullable|string|in:asc,desc',
            ]);

            $perPage   = $validated['per_page'] ?? 15;
            $sortBy    = $validated['sort_by'] ?? 'created_at';
            $sortOrder = $validated['sort_order'] ?? 'desc';

            // Build query with search functionality
            $query = AccessRights::query();

            // Search across multiple fields
            if (! empty($validated['search'])) {
                $searchTerm = $validated['search'];
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('route_name', 'like', "%{$searchTerm}%")
                        ->orWhere('short_id', 'like', "%{$searchTerm}%")
                        ->orWhere('short_description', 'like', "%{$searchTerm}%");
                });
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $accessRights = $query->paginate($perPage);

            // Log successful retrieval
            Log::info('Access rights list retrieved', [
                'total'  => $accessRights->total(),
                'page'   => $accessRights->currentPage(),
                'search' => $validated['search'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Access rights retrieved successfully.',
                'data'    => AccessRightsResource::collection($accessRights),
                'meta'    => [
                    'current_page' => $accessRights->currentPage(),
                    'last_page'    => $accessRights->lastPage(),
                    'per_page'     => $accessRights->perPage(),
                    'total'        => $accessRights->total(),
                    'from'         => $accessRights->firstItem(),
                    'to'           => $accessRights->lastItem(),
                ],
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('Validation failed for access rights list', [
                'errors' => $e->errors(),
                'ip'     => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve access rights list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve access rights. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/access-right-show/{id}",
     *     summary="Get a specific access right by ID",
     *     tags={"Access Rights"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Access right ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Access right retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Access right retrieved successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/AccessRights")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Access right not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            // Find access right or fail
            $accessRight = AccessRights::findOrFail($id);

            // Log successful retrieval
            Log::info('Access right retrieved successfully', [
                'id'              => $accessRight->id,
                'route_name'      => $accessRight->route_name,
                'requested_by_ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Access right retrieved successfully.',
                'data'    => new AccessRightsResource($accessRight),
            ], 200);

        } catch (ModelNotFoundException $e) {
            Log::warning('Access right not found for show', [
                'id' => $id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Access right not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve access right', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve access right. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/access-right-create",
     *     summary="Create a new access right",
     *     tags={"Access Rights"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"route_name", "short_id", "short_description"},
     *             @OA\Property(property="route_name", type="string", minLength=5, maxLength=200, example="api.admin.users.index"),
     *             @OA\Property(property="short_id", type="string", minLength=5, maxLength=200, example="users_list"),
     *             @OA\Property(property="short_description", type="string", minLength=5, maxLength=300, example="View list of users"),
     *             @OA\Property(property="details", type="string", nullable=true, minLength=10, maxLength=1000, example="This permission allows viewing all users in the system")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Access right created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Access right created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/AccessRights")
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
            // Validate input
            $validated = $request->validate([
                'route_name'        => 'required|string|min:5|max:200|unique:access_rights,route_name',
                'short_id'          => 'required|string|min:5|max:200|unique:access_rights,short_id',
                'short_description' => 'required|string|min:5|max:300',
                'details'           => 'nullable|string|min:10|max:1000',
            ]);

            // Create access right
            $accessRight = AccessRights::create($validated);

            DB::commit();

            // Log successful creation
            Log::info('Access right created successfully', [
                'id'            => $accessRight->id,
                'route_name'    => $accessRight->route_name,
                'created_by_ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Access right created successfully.',
                'data'    => new AccessRightsResource($accessRight),
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();

            Log::warning('Validation failed for access right creation', [
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

            Log::error('Failed to create access right', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create access right. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/access-right-update/{id}",
     *     summary="Update an existing access right",
     *     tags={"Access Rights"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Access right ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"route_name", "short_id", "short_description"},
     *             @OA\Property(property="route_name", type="string", minLength=5, maxLength=200, example="api.admin.users.index"),
     *             @OA\Property(property="short_id", type="string", minLength=5, maxLength=200, example="users_list"),
     *             @OA\Property(property="short_description", type="string", minLength=5, maxLength=300, example="View list of users"),
     *             @OA\Property(property="details", type="string", nullable=true, minLength=10, maxLength=1000, example="This permission allows viewing all users in the system")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Access right updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Access right updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/AccessRights")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Access right not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Find access right or fail
            $accessRight = AccessRights::findOrFail($id);

            // Validate input with unique rules excluding current record
            $validated = $request->validate([
                'route_name' => "required|string|min:5|max:200|unique:access_rights,route_name,{$id}",
                'short_id' => "required|string|min:5|max:200|unique:access_rights,short_id,{$id}",
                'short_description' => 'required|string|min:5|max:300',
                'details'           => 'nullable|string|min:10|max:1000',
            ]);

            // Update access right
            $accessRight->update($validated);

            DB::commit();

            // Log successful update
            Log::info('Access right updated successfully', [
                'id'            => $accessRight->id,
                'route_name'    => $accessRight->route_name,
                'updated_by_ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Access right updated successfully.',
                'data'    => new AccessRightsResource($accessRight),
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            Log::warning('Access right not found for update', [
                'id' => $id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Access right not found.',
            ], 404);

        } catch (ValidationException $e) {
            DB::rollBack();

            Log::warning('Validation failed for access right update', [
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

            Log::error('Failed to update access right', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update access right. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/access-right-delete/{id}",
     *     summary="Delete an access right",
     *     tags={"Access Rights"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Access right ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Access right deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Access right deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Access right not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Find access right or fail
            $accessRight = AccessRights::findOrFail($id);

            // Store info for logging before deletion
            $routeName = $accessRight->route_name;
            $shortId   = $accessRight->short_id;

            // Delete access right
            $accessRight->delete();

            DB::commit();

            // Log successful deletion
            Log::info('Access right deleted successfully', [
                'id'            => $id,
                'route_name'    => $routeName,
                'short_id'      => $shortId,
                'deleted_by_ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Access right deleted successfully.',
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            Log::warning('Access right not found for deletion', [
                'id' => $id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Access right not found.',
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete access right', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete access right. Please try again.',
            ], 500);
        }
    }
}
