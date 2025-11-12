<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductStockResource;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="ProductStock",
 *     description="API endpoints for managing ProductStock"
 * )
 */

/**
 * @OA\Schema(
 *     schema="ProductStock",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="product_id", type="integer", example=5),
 *     @OA\Property(property="quantity", type="integer", example=20),
 *     @OA\Property(property="buying_price", type="number", format="float", example=120.50),
 *     @OA\Property(property="selling_price", type="number", format="float", example=150.75),
 *     @OA\Property(property="supplier_id", type="integer", example=2),
 *     @OA\Property(property="discount_type", type="string", example="percentage"),
 *     @OA\Property(property="discount_value", type="number", format="float", example=10.00),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="updated_by", type="integer", example=1),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="product", type="object", ref="#/components/schemas/Product"),
 *     @OA\Property(property="supplier", type="object", ref="#/components/schemas/Supplier"),
 *     @OA\Property(property="order_items_count", type="integer", example=5),
 *     @OA\Property(property="creator", type="object", ref="#/components/schemas/User"),
 *     @OA\Property(property="updater", type="object", ref="#/components/schemas/User"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ProductStockController extends Controller
{
    /**
     * List ProductStocks.
     *
     * @OA\Get(
     *     path="/api/product-stock-list",
     *     tags={"ProductStock"},
     *     summary="Get paginated list of product stocks",
     *     @OA\Parameter(name="q", in="query", description="Search by product id or supplier id", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="List retrieved")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_by' => ['nullable', 'string', Rule::in(['id', 'quantity', 'created_at'])],
            'sort_dir' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=>false,'message'=>'Invalid parameters','errors'=>$validator->errors()],422);
        }

        try {
            $perPage = $request->input('per_page',15);
            $sortBy = $request->input('sort_by','created_at');
            $sortDir = $request->input('sort_dir','desc');

            $query = ProductStock::with(['product','supplier','creator','updater'])->withCount('orderItems');

            if ($q = $request->input('q')) {
                $query->where('id','like',"%{$q}%")
                      ->orWhere('product_id','like',"%{$q}%")
                      ->orWhere('supplier_id','like',"%{$q}%");
            }

            if (!is_null($request->input('is_active'))) {
                $query->where('is_active',$request->boolean('is_active'));
            }

            $query->orderBy($sortBy,$sortDir);
            $paginator = $query->paginate($perPage)->appends($request->query());

            return response()->json([
                'success'=>true,
                'message'=>'Product stocks retrieved',
                'data'=>ProductStockResource::collection($paginator->items()),
                'meta'=>[
                    'current_page'=>$paginator->currentPage(),
                    'last_page'=>$paginator->lastPage(),
                    'per_page'=>$paginator->perPage(),
                    'total'=>$paginator->total(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('ProductStock index error',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to retrieve'],500);
        }
    }

    /**
     * Show a ProductStock.
     *
     * @OA\Get(
     *     path="/api/product-stock-show/{id}",
     *     tags={"ProductStock"},
     *     summary="Get single product stock",
     *     @OA\Parameter(name="id",in="path",required=true,@OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Retrieved")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $item = ProductStock::with(['product','supplier','creator','updater'])->withCount('orderItems')->find($id);
        if(!$item){
            return response()->json(['success'=>false,'message'=>'Not found'],404);
        }
        return response()->json(['success'=>true,'message'=>'Product stock retrieved','data'=>new ProductStockResource($item)]);
    }

    /**
     * Store ProductStock.
     *
     * @OA\Post(
     *     path="/api/product-stock-create",
     *     tags={"ProductStock"},
     *     summary="Create product stock",
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/ProductStock")),
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'product_id' => ['required','integer','exists:products,id'],
            'quantity' => ['required','integer','min:1'],
            'buying_price' => ['required','numeric','min:0'],
            'selling_price' => ['required','numeric','min:0'],
            'supplier_id' => ['required','integer','exists:suppliers,id'],
            'discount_type' => ['required', Rule::in(['fixed','percentage'])],
            'discount_value' => ['required','numeric','min:0'],
            'created_by' => ['required','integer','exists:users,id'],
            'updated_by' => ['required','integer','exists:users,id'],
            'is_active' => ['required','boolean'],
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);
        }

        DB::beginTransaction();
        try {
            $item = ProductStock::create($request->only(array_keys($rules)));
            DB::commit();
            Log::info('ProductStock created',['user_id'=>Auth::id(),'id'=>$item->id]);
            return response()->json(['success'=>true,'message'=>'Product stock created','data'=>new ProductStockResource($item->load(['product','supplier','creator','updater']))],201);
        } catch(\Throwable $e){
            DB::rollBack();
            Log::error('ProductStock create failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to create'],500);
       
                    }
    }

    /**
     * Update ProductStock.
     *
     * @OA\Put(
     *     path="/api/product-stock-update/{id}",
     *     tags={"ProductStock"},
     *     summary="Update product stock",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/ProductStock")),
     *     @OA\Response(response=200, description="Updated")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $item = ProductStock::find($id);
        if (!$item) {
            return response()->json(['success'=>false,'message'=>'Not found'],404);
        }

        $rules = [
            'product_id' => ['sometimes','integer','exists:products,id'],
            'quantity' => ['sometimes','integer','min:1'],
            'buying_price' => ['sometimes','numeric','min:0'],
            'selling_price' => ['sometimes','numeric','min:0'],
            'supplier_id' => ['sometimes','integer','exists:suppliers,id'],
            'discount_type' => ['sometimes', Rule::in(['fixed','percentage'])],
            'discount_value' => ['sometimes','numeric','min:0'],
            'updated_by' => ['sometimes','integer','exists:users,id'],
            'is_active' => ['sometimes','boolean'],
        ];

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);
        }

        DB::beginTransaction();
        try {
            $item->fill($request->only(array_keys($rules)))->save();
            DB::commit();
            Log::info('ProductStock updated',['user_id'=>Auth::id(),'id'=>$item->id]);
            return response()->json([
                'success'=>true,
                'message'=>'Product stock updated',
                'data'=>new ProductStockResource($item->load(['product','supplier','creator','updater'])->loadCount('orderItems'))
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ProductStock update failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to update'],500);
        }
    }

    /**
     * Delete ProductStock.
     *
     * @OA\Delete(
     *     path="/api/product-stock-delete/{id}",
     *     tags={"ProductStock"},
     *     summary="Delete product stock",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $item = ProductStock::withCount('orderItems')->find($id);
        if (!$item) {
            return response()->json(['success'=>false,'message'=>'Not found'],404);
        }

        // Business logic: prevent deletion if there are order items
        if ($item->order_items_count > 0) {
            return response()->json(['success'=>false,'message'=>'Cannot delete stock linked to orders'],400);
        }

        DB::beginTransaction();
        try {
            $item->delete();
            DB::commit();
            Log::info('ProductStock deleted',['user_id'=>Auth::id(),'id'=>$id]);
            return response()->json(['success'=>true,'message'=>'Product stock deleted']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ProductStock delete failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to delete'],500);
        }
    }
}
