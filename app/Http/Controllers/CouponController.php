<?php
namespace App\Http\Controllers;

use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Coupons",
 *     description="API endpoints for managing coupons"
 * )
 *
 * @OA\Schema(
 *     schema="Coupon",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="SAVE10"),
 *     @OA\Property(property="type", type="string", example="fixed"),
 *     @OA\Property(property="value", type="number", format="decimal", example=10.50),
 *     @OA\Property(property="min_order_amount", type="number", format="decimal", nullable=true, example=50.00),
 *     @OA\Property(property="max_discount_amount", type="number", format="decimal", nullable=true, example=100.00),
 *     @OA\Property(property="start_date", type="string", format="date-time", example="2025-01-01T00:00:00"),
 *     @OA\Property(property="end_date", type="string", format="date-time", example="2025-12-31T23:59:59"),
 *     @OA\Property(property="usage_limit", type="integer", nullable=true, example=100),
 *     @OA\Property(property="used_count", type="integer", example=0),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="updated_by", type="integer", nullable=true, example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="creator", type="object", ref="#/components/schemas/User"),
 *     @OA\Property(property="updater", type="object", ref="#/components/schemas/User")
 * )
 */
class CouponController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/coupon-list",
     *     summary="Get paginated list of coupons",
     *     tags={"Coupons"},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", example="SAVE")),
     *     @OA\Parameter(name="is_active", in="query", required=false, @OA\Schema(type="boolean", example=true)),
     *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string", example="start_date")),
     *     @OA\Parameter(name="sort_order", in="query", required=false, @OA\Schema(type="string", enum={"asc","desc"}, example="asc")),
     *     @OA\Response(response=200, description="Coupons retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Coupons retrieved successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Coupon")),
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
                'sort_by'    => 'nullable|string|in:code,type,value,start_date,end_date,created_at,updated_at',
                'sort_order' => 'nullable|string|in:asc,desc',
            ]);

            $perPage   = $validated['per_page'] ?? 15;
            $sortBy    = $validated['sort_by'] ?? 'created_at';
            $sortOrder = $validated['sort_order'] ?? 'desc';

            $query = Coupon::with(['creator', 'updater']);

            if (! empty($validated['search'])) {
                $search = $validated['search'];
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            }

            if (isset($validated['is_active'])) {
                $query->where('is_active', $validated['is_active']);
            }

            $query->orderBy($sortBy, $sortOrder);

            $coupons = $query->paginate($perPage);

            Log::info('Coupons list retrieved', [
                'total' => $coupons->total(),
                'page'  => $coupons->currentPage(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Coupons retrieved successfully.',
                'data'    => CouponResource::collection($coupons),
                'meta'    => [
                    'current_page' => $coupons->currentPage(),
                    'last_page'    => $coupons->lastPage(),
                    'per_page'     => $coupons->perPage(),
                    'total'        => $coupons->total(),
                    'from'         => $coupons->firstItem(),
                    'to'           => $coupons->lastItem(),
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve coupons', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve coupons. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/coupon-show/{id}",
     *     summary="Get a specific coupon by ID",
     *     tags={"Coupons"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Coupon retrieved successfully", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Coupon retrieved successfully."),
     *         @OA\Property(property="data", ref="#/components/schemas/Coupon")
     *     )),
     *     @OA\Response(response=404, description="Coupon not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $coupon = Coupon::with(['creator', 'updater'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Coupon retrieved successfully.',
                'data'    => new CouponResource($coupon),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve coupon', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve coupon. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/coupon-create",
     *     summary="Create a new coupon",
     *     tags={"Coupons"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"code","type","value","start_date","end_date"},
     *         @OA\Property(property="code", type="string", example="SAVE10"),
     *         @OA\Property(property="type", type="string", enum={"fixed","percentage"}, example="fixed"),
     *         @OA\Property(property="value", type="number", format="decimal", example=10.50),
     *         @OA\Property(property="min_order_amount", type="number", format="decimal", nullable=true, example=50.00),
     *         @OA\Property(property="max_discount_amount", type="number", format="decimal", nullable=true, example=100.00),
     *         @OA\Property(property="start_date", type="string", format="date-time", example="2025-01-01T00:00:00"),
     *         @OA\Property(property="end_date", type="string", format="date-time", example="2025-12-31T23:59:59"),
     *         @OA\Property(property="usage_limit", type="integer", nullable=true, example=100),
     *         @OA\Property(property="is_active", type="boolean", example=true)
     *     )),
     *     @OA\Response(response=201, description="Coupon created successfully", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Coupon created successfully."),
     *         @OA\Property(property="data", ref="#/components/schemas/Coupon")
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
                'code'                => 'required|string|unique:coupons,code',
                'type'                => 'required|string|in:fixed,percentage',
                'value'               => 'required|numeric|min:0',
                'min_order_amount'    => 'nullable|numeric|min:0',
                'max_discount_amount' => 'nullable|numeric|min:0',
                'start_date'          => 'required|date',
                'end_date'            => 'required|date|after_or_equal:start_date',
                'usage_limit'         => 'nullable|integer|min:0',
                'is_active'           => 'nullable|boolean',
            ]);

            $validated['created_by'] = $userId;
            $validated['updated_by'] = $userId;
            $validated['used_count'] = 0;
            $validated['is_active']  = $validated['is_active'] ?? true;

            $coupon = Coupon::create($validated);
            $coupon->load(['creator', 'updater']);

            DB::commit();

            Log::info('Coupon created', ['id' => $coupon->id, 'created_by' => $userId]);

            return response()->json([
                'success' => true,
                'message' => 'Coupon created successfully.',
                'data'    => new CouponResource($coupon),
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
            Log::error('Failed to create coupon', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create coupon. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/coupon-update/{id}",
     *     summary="Update a coupon",
     *     tags={"Coupons"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"code","type","value","start_date","end_date"},
     *         @OA\Property(property="code", type="string", example="SAVE10"),
     *         @OA\Property(property="type", type="string", enum={"fixed","percentage"}, example="fixed"),
     *         @OA\Property(property="value", type="number", format="decimal", example=10.50),
     *         @OA\Property(property="min_order_amount", type="number", format="decimal", nullable=true, example=50.00),
     *         @OA\Property(property="max_discount_amount", type="number", format="decimal", nullable=true, example=100.00),
     *         @OA\Property(property="start_date", type="string", format="date-time", example="2025-01-01T00:00:00"),
     *         @OA\Property(property="end_date", type="string", format="date-time", example="2025-12-31T23:59:59"),
     *         @OA\Property(property="usage_limit", type="integer", nullable=true, example=100),
     *         @OA\Property(property="is_active", type="boolean", example=true)
     *     )),
     *     @OA\Response(response=200, description="Coupon updated successfully", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Coupon updated successfully."),
     *         @OA\Property(property="data", ref="#/components/schemas/Coupon")
     *     )),
     *     @OA\Response(response=404, description="Coupon not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $coupon = Coupon::findOrFail($id);
            $userId = auth()->id() ?? 1;

            $validated = $request->validate([
                'code' => "required|string|unique:coupons,code,{$id}",
                'type'                => 'required|string|in:fixed,percentage',
                'value'               => 'required|numeric|min:0',
                'min_order_amount'    => 'nullable|numeric|min:0',
                'max_discount_amount' => 'nullable|numeric|min:0',
                'start_date'          => 'required|date',
                'end_date'            => 'required|date|after_or_equal:start_date',
                'usage_limit'         => 'nullable|integer|min:0',
                'is_active'           => 'nullable|boolean',
            ]);

            $validated['updated_by'] = $userId;

            $coupon->update($validated);
            $coupon->load(['creator', 'updater']);

            DB::commit();
            Log::info('Coupon updated', ['id' => $coupon->id, 'updated_by' => $userId]);

            return response()->json([
                'success' => true,
                'message' => 'Coupon updated successfully.',
                'data'    => new CouponResource($coupon),
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found.',
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
            Log::error('Failed to update coupon', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update coupon. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/coupon-delete/{id}",
     *     summary="Delete a coupon",
     *     tags={"Coupons"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Coupon deleted successfully", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Coupon deleted successfully.")
     *     )),
     *     @OA\Response(response=404, description="Coupon not found"),
     *     @OA\Response(response=422, description="Cannot delete coupon with usage"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $coupon = Coupon::findOrFail($id);

            if ($coupon->used_count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete coupon that has been used.',
                ], 422);
            }

            $coupon->delete();
            DB::commit();

            Log::info('Coupon deleted', ['id' => $coupon->id, 'deleted_by' => auth()->id() ?? 1]);

            return response()->json([
                'success' => true,
                'message' => 'Coupon deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete coupon', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete coupon. Please try again.',
            ], 500);
        }
    }
}
