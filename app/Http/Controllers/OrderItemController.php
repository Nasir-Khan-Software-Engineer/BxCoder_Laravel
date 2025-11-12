<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemResource;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="OrderItem",
 *     description="API endpoints for managing OrderItem"
 * )
 */

/**
 * @OA\Schema(
 *     schema="OrderItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=5),
 *     @OA\Property(property="product_id", type="integer", example=10),
 *     @OA\Property(property="product_name", type="string", example="Laptop"),
 *     @OA\Property(property="discount_type", type="string", example="percentage"),
 *     @OA\Property(property="discount_value", type="number", format="float", example=10.00, nullable=true),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="unit_price", type="number", format="float", example=500.00),
 *     @OA\Property(property="total_price", type="number", format="float", example=900.00),
 *     @OA\Property(property="order", type="object", ref="#/components/schemas/Order"),
 *     @OA\Property(property="product", type="object", ref="#/components/schemas/Product"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class OrderItemController extends Controller
{
    /**
     * List OrderItems.
     *
     * @OA\Get(
     *     path="/api/order-item-list",
     *     tags={"OrderItem"},
     *     summary="Get paginated list of order items",
     *     @OA\Parameter(name="q", in="query", description="Search by product_name or order_id", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_by", in="query", description="Sort field (quantity, unit_price, total_price, created_at)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_dir", in="query", description="Sort direction (asc|desc)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="List retrieved")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => ['nullable','string'],
            'sort_by' => ['nullable','string', Rule::in(['quantity','unit_price','total_price','created_at'])],
            'sort_dir' => ['nullable','string', Rule::in(['asc','desc'])],
            'per_page' => ['nullable','integer','min:1','max:200'],
        ]);

        if($validator->fails()){
            return response()->json(['success'=>false,'message'=>'Invalid parameters','errors'=>$validator->errors()],422);
        }

        try {
            $perPage = $request->input('per_page',15);
            $sortBy = $request->input('sort_by','created_at');
            $sortDir = $request->input('sort_dir','desc');

            $query = OrderItem::with(['order','product']);

            if($q = $request->input('q')){
                $query->where('product_name','like',"%{$q}%")
                      ->orWhere('order_id','like',"%{$q}%");
            }

            $query->orderBy($sortBy,$sortDir);
            $paginator = $query->paginate($perPage)->appends($request->query());

            return response()->json([
                'success'=>true,
                'message'=>'Order items retrieved',
                'data'=>OrderItemResource::collection($paginator->items()),
                'meta'=>[
                    'current_page'=>$paginator->currentPage(),
                    'last_page'=>$paginator->lastPage(),
                    'per_page'=>$paginator->perPage(),
                    'total'=>$paginator->total(),
                ],
            ]);

        } catch (\Throwable $e){
            Log::error('OrderItem index failed',['user_id'=>Auth::id(),'ip'=>$request->ip(),'error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to retrieve order items'],500);
        }
    }

    /**
     * Show single OrderItem.
     *
     * @OA\Get(
     *     path="/api/order-item-show/{id}",
     *     tags={"OrderItem"},
     *     summary="Get single order item",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Order item retrieved")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $item = OrderItem::with(['order','product'])->find($id);
        if(!$item){
            return response()->json(['success'=>false,'message'=>'Order item not found'],404);
        }
        return response()->json(['success'=>true,'message'=>'Order item retrieved','data'=>new OrderItemResource($item)]);
    }

    /**
     * Create OrderItem.
     *
     * @OA\Post(
     *     path="/api/order-item-create",
     *     tags={"OrderItem"},
     *     summary="Create new order item",
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/OrderItem")),
     *     @OA\Response(response=201, description="Order item created")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'order_id' => ['required','integer','exists:orders,id'],
            'product_id' => ['required','integer','exists:products,id'],
            'product_name' => ['required','string','min:1'],
            'discount_type' => ['nullable', Rule::in(['fixed','percentage'])],
            'discount_value' => ['nullable','numeric','min:0'],
            'quantity' => ['required','integer','min:1'],
            'unit_price' => ['required','numeric','min:0'],
            'total_price' => ['required','numeric','min:0'],
        ];

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);
        }

        DB::beginTransaction();
        try{
            $item = OrderItem::create($request->only(array_keys($rules)));
            DB::commit();
            Log::info('OrderItem created',['user_id'=>Auth::id(),'order_item_id'=>$item->id]);
            return response()->json(['success'=>true,'message'=>'Order item created','data'=>new OrderItemResource($item->load(['order','product']))],201);
        } catch (\Throwable $e){
            DB::rollBack();
            Log::error('OrderItem creation failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to create order item'],500);
        }
    }

    /**
     * Update OrderItem.
     *
     * @OA\Put(
     *     path="/api/order-item-update/{id}",
     *     tags={"OrderItem"},
     *     summary="Update order item",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/OrderItem")),
     *     @OA\Response(response=200, description="Order item updated")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $item = OrderItem::find($id);
        if(!$item){
            return response()->json(['success'=>false,'message'=>'Order item not found'],404);
        }

        $rules = [
            'order_id' => ['sometimes','integer','exists:orders,id'],
            'product_id' => ['sometimes','integer','exists:products,id'],
            'product_name' => ['sometimes','string','min:1'],
            'discount_type' => ['nullable', Rule::in(['fixed','percentage'])],
            'discount_value' => ['nullable','numeric','min:0'],
            'quantity' => ['sometimes','integer','min:1'],
            'unit_price' => ['sometimes','numeric','min:0'],
            'total_price' => ['sometimes','numeric','min:0'],
        ];

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);
        }

        DB::beginTransaction();
        try{
            $item->fill($request->only(array_keys($rules)))->save();
            DB::commit();
            Log::info('OrderItem updated',['user_id'=>Auth::id(),'order_item_id'=>$item->id]);
            return response()->json(['success'=>true,'message'=>'Order item updated','data'=>new OrderItemResource($item->load(['order','product']))]);
        } catch (\Throwable $e){
            DB::rollBack();
            Log::error('OrderItem update failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to update order item'],500);
        }
    }

    /**
     * Delete OrderItem.
     *
     * @OA\Delete(
     *     path="/api/order-item-delete/{id}",
     *     tags={"OrderItem"},
     *     summary="Delete order item",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Order item deleted")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $item = OrderItem::find($id);
        if(!$item){
            return response()->json(['success'=>false,'message'=>'Order item not found'],404);
        }

        DB::beginTransaction();
        try{
            $item->delete();
            DB::commit();
            Log::info('OrderItem deleted',['user_id'=>Auth::id(),'order_item_id'=>$id]);
            return response()->json(['success'=>true,'message'=>'Order item deleted']);
        } catch (\Throwable $e){
            DB::rollBack();
            Log::error('OrderItem delete failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to delete order item'],500);
        }
    }
}
