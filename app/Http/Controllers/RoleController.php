<?php
namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Roles",
 *     description="API endpoints for managing roles"
 * )
 *
 * @OA\Schema(
 *     schema="Role",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Administrator"),
 *     @OA\Property(property="description", type="string", example="Full access to system"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="is_default", type="boolean", example=false),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="updated_by", type="integer", nullable=true, example=2),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="users_count", type="integer", example=5),
 *     @OA\Property(property="users", type="array", @OA\Items(ref="#/components/schemas/User"))
 * )
 */
class RoleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/role-list",
     *     summary="Get paginated list of roles",
     *     tags={"Roles"},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", example="Admin")),
     *     @OA\Parameter(name="is_active", in="query", required=false, @OA\Schema(type="boolean", example=true)),
     *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string", example="name")),
     *     @OA\Parameter(name="sort_order", in="query", required=false, @OA\Schema(type="string", enum={"asc","desc"}, example="asc")),
     *     @OA\Response(response=200, description="Roles retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Roles retrieved successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Role")),
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
                'search'     => 'nullable|string|max:255',
                'is_active'  => 'nullable|boolean',
                'sort_by'    => 'nullable|string|in:name,description,is_active,created_at,updated_at',
                'sort_order' => 'nullable|string|in:asc,desc',
            ]);

            $perPage   = $validated['per_page'] ?? 15;
            $sortBy    = $validated['sort_by'] ?? 'created_at';
            $sortOrder = $validated['sort_order'] ?? 'desc';

            $query = Role::withCount('users')->with('users');

            if (! empty($validated['search'])) {
                $search = $validated['search'];
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }

            if (isset($validated['is_active'])) {
                $query->where('is_active', $validated['is_active']);
            }

            $query->orderBy($sortBy, $sortOrder);

            $roles = $query->paginate($perPage);

            Log::info('Roles list retrieved', [
                'total' => $roles->total(),
                'page'  => $roles->currentPage(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Roles retrieved successfully.',
                'data'    => RoleResource::collection($roles),
                'meta'    => [
                    'current_page' => $roles->currentPage(),
                    'last_page'    => $roles->lastPage(),
                    'per_page'     => $roles->perPage(),
                    'total'        => $roles->total(),
                    'from'         => $roles->firstItem(),
                    'to'           => $roles->lastItem(),
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve roles', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles. Please try again.' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/role-show/{id}",
     *     summary="Get a specific role by ID",
     *     tags={"Roles"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Role retrieved successfully", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Role retrieved successfully."),
     *         @OA\Property(property="data", ref="#/components/schemas/Role")
     *     )),
     *     @OA\Response(response=404, description="Role not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $role = Role::withCount('users')->with('users')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Role retrieved successfully.',
                'data'    => new RoleResource($role),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve role', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve role. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/role-create",
     *     summary="Create a new role",
     *     tags={"Roles"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","description","created_by"},
     *         @OA\Property(property="name", type="string", example="Administrator"),
     *         @OA\Property(property="description", type="string", example="Full access to system"),
     *         @OA\Property(property="is_active", type="boolean", example=true),
     *         @OA\Property(property="is_default", type="boolean", example=false),
     *         @OA\Property(property="created_by", type="integer", example=1)
     *     )),
     *     @OA\Response(response=201, description="Role created successfully", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Role created successfully."),
     *         @OA\Property(property="data", ref="#/components/schemas/Role")
     *     )),
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
                'name'        => 'required|string|min:5|max:100|unique:roles,name',
                'description' => 'required|string|min:5|max:1000',
                'is_active'   => 'nullable|boolean',
                'is_default'  => 'nullable|boolean',
            ]);

            $validated['created_by'] = $userId;
            $validated['updated_by'] = $userId;
            $validated['is_active']  = $validated['is_active'] ?? true;
            $validated['is_default'] = $validated['is_default'] ?? false;

            $role = Role::create($validated);
            $role->load('users');

            DB::commit();
            Log::info('Role created', ['id' => $role->id, 'created_by' => $userId]);

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
                'data'    => new RoleResource($role),
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create role', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/role-update/{id}",
     *     summary="Update a role",
     *     tags={"Roles"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","description"},
     *         @OA\Property(property="name", type="string", example="Administrator"),
     *         @OA\Property(property="description", type="string", example="Full access to system"),
     *         @OA\Property(property="is_active", type="boolean", example=true),
     *         @OA\Property(property="is_default", type="boolean", example=false)
     *     )),
     *     @OA\Response(response=200, description="Role updated successfully", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Role updated successfully."),
     *         @OA\Property(property="data", ref="#/components/schemas/Role")
     *     )),
     *     @OA\Response(response=404, description="Role not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $role   = Role::findOrFail($id);
            $userId = auth()->id() ?? 1;

            $validated = $request->validate([
                'name' => "required|string|min:5|max:100|unique:roles,name,{$id}",
                'description' => 'required|string|min:5|max:1000',
                'is_active'   => 'nullable|boolean',
                'is_default'  => 'nullable|boolean',
            ]);

            $validated['updated_by'] = $userId;

            $role->update($validated);
            $role->load('users');

            DB::commit();
            Log::info('Role updated', ['id' => $role->id, 'updated_by' => $userId]);

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.',
                'data'    => new RoleResource($role),
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Role not found.',
            ], 404);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update role', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/role-delete/{id}",
     *     summary="Delete a role",
     *     tags={"Roles"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Role deleted successfully", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Role deleted successfully.")
     *     )),
     *     @OA\Response(response=404, description="Role not found"),
     *     @OA\Response(response=422, description="Cannot delete role with users"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $role = Role::withCount('users')->findOrFail($id);

            if ($role->users_count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role with assigned users.',
                ], 422);
            }

            $role->delete();
            DB::commit();

            Log::info('Role deleted', ['id' => $role->id, 'deleted_by' => auth()->id() ?? 1]);

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Role not found.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete role', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role. Please try again.',
            ], 500);
        }
    }
}
