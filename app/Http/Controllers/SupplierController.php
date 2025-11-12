<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Supplier",
 *     description="API endpoints for managing Supplier"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Supplier",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Acme Supplies"),
 *     @OA\Property(property="email", type="string", example="supplier@example.com"),
 *     @OA\Property(property="phone", type="string", example="+123456789"),
 *     @OA\Property(property="address", type="string", example="123 Main St"),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="updated_by", type="integer", example=2),
 *     @OA\Property(
 *         property="creator",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="John Doe")
 *     ),
 *     @OA\Property(
 *         property="updater",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="name", type="string", example="Jane Doe")
 *     ),
 *     @OA\Property(
 *         property="productStocks",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=10),
 *             @OA\Property(property="product_name", type="string", example="Widget A"),
 *             @OA\Property(property="quantity", type="integer", example=100)
 *         )
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SupplierController extends Controller
{
    /**
     * Display a paginated listing of suppliers.
     *
     * @OA\Get(
     *     path="/api/supplier-list",
     *     tags={"Supplier"},
     *     summary="List suppliers with pagination, search, filter, and sorting",
     *     @OA\Parameter(name="search", in="query", description="Search by name or email", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", description="Filter by status", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="sort_by", in="query", description="Field to sort by", required=false, @OA\Schema(type="string", example="created_at")),
     *     @OA\Parameter(name="sort_order", in="query", description="Sort order", required=false, @OA\Schema(type="string", enum={"asc","desc"}, example="desc")),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Suppliers retrieved successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Supplier")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Supplier::with(['creator', 'updater', 'productStocks']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (! is_null($request->query('status'))) {
            $query->where('status', $request->query('status'));
        }

        $sortBy    = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');

        $suppliers = $query->orderBy($sortBy, $sortOrder)->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Suppliers retrieved successfully.',
            'data'    => SupplierResource::collection($suppliers),
            'meta'    => [
                'current_page' => $suppliers->currentPage(),
                'last_page'    => $suppliers->lastPage(),
                'per_page'     => $suppliers->perPage(),
                'total'        => $suppliers->total(),
            ],
        ]);
    }

    /**
     * Display a single supplier.
     *
     * @OA\Get(
     *     path="/api/supplier-show/{id}",
     *     tags={"Supplier"},
     *     summary="Get a single supplier",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Supplier retrieved successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Supplier")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Supplier not found")
     * )
     */
    public function show(int $id)
    {
        $supplier = Supplier::with(['creator', 'updater', 'productStocks'])->find($id);

        if (! $supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Supplier retrieved successfully.',
            'data'    => new SupplierResource($supplier),
        ]);
    }

    /**
     * Store a newly created supplier.
     *
     * @OA\Post(
     *     path="/api/supplier-create",
     *     tags={"Supplier"},
     *     summary="Create a new supplier",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","phone","status","created_by"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="created_by", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Supplier created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Supplier created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Supplier")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|min:3|max:200',
            'email'      => 'required|string|email|max:200|unique:suppliers,email',
            'phone'      => 'required|string|max:20',
            'address'    => 'nullable|string|max:1000',
            'status'     => 'required|boolean',
            'created_by' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors'  => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $supplier = Supplier::create(array_merge($request->all(), [
                'updated_by' => $request->created_by,
            ]));

            DB::commit();

            Log::info('Supplier created', ['supplier_id' => $supplier->id, 'user_id' => $request->created_by, 'ip' => $request->ip()]);

            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully.',
                'data'    => new SupplierResource($supplier),
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Supplier creation failed', ['error' => $e->getMessage(), 'ip' => $request->ip()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create supplier.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified supplier.
     *
     * @OA\Put(
     *     path="/api/supplier-update/{id}",
     *     tags={"Supplier"},
     *     summary="Update an existing supplier",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="updated_by", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Supplier updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Supplier")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Supplier not found")
     * )
     */
    public function update(Request $request, int $id)
    {
        $supplier = Supplier::find($id);

        if (! $supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'name'       => 'sometimes|required|string|min:3|max:200',
            'email'      => 'sometimes|required|string|email|max:200|unique:suppliers,email,' . $id,
            'phone'      => 'sometimes|required|string|max:20',
            'address'    => 'nullable|string|max:1000',
            'status'     => 'sometimes|required|boolean',
            'updated_by' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors'  => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $supplier->update($request->only(['name', 'email', 'phone', 'address', 'status', 'updated_by']));

            DB::commit();

            Log::info('Supplier updated', ['supplier_id' => $supplier->id, 'user_id' => $request->updated_by, 'ip' => $request->ip()]);

            return response()->json([
                'success' => true,
                'message' => 'Supplier updated successfully.',
                'data'    => new SupplierResource($supplier),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Supplier update failed', ['error' => $e->getMessage(), 'supplier_id' => $supplier->id, 'ip' => $request->ip()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update supplier.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified supplier.
     *
     * @OA\Delete(
     *     path="/api/supplier-delete/{id}",
     *     tags={"Supplier"},
     *     summary="Delete a supplier",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Supplier deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Supplier not found")
     * )
     */
    public function destroy(int $id, Request $request)
    {
        $supplier = Supplier::find($id);

        if (! $supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();

            // Business logic: prevent deletion if supplier has products
            if ($supplier->productStocks()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete supplier with associated products.',
                ], Response::HTTP_FORBIDDEN);
            }

            $supplier->delete();

            DB::commit();

            Log::info('Supplier deleted', ['supplier_id' => $id, 'ip' => $request->ip()]);

            return response()->json([
                'success' => true,
                'message' => 'Supplier deleted successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Supplier deletion failed', ['error' => $e->getMessage(), 'supplier_id' => $id, 'ip' => $request->ip()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete supplier.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
