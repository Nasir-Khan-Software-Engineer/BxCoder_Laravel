<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Order",
 *     description="API endpoints for managing Orders"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="invoice_no", type="string", example="INV20250001"),
 *     @OA\Property(property="order_date", type="string", format="date-time"),
 *     @OA\Property(property="user_id", type="integer", example=2),
 *     @OA\Property(property="total_price", type="number", format="float", example=1200.50),
 *     @OA\Property(property="total_items", type="integer", example=3),
 *     @OA\Property(property="total_qty", type="integer", example=5),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="note", type="string", nullable=true),
 *     @OA\Property(property="is_inside_dhaka", type="boolean", example=true),
 *     @OA\Property(property="shipping_cost", type="number", format="float", example=50.00),
 *     @OA\Property(property="payment_method", type="string", example="cash"),
 *     @OA\Property(property="payment_status", type="string", example="pending"),
 *     @OA\Property(property="address", type="string", example="123 Street, Dhaka"),
 *     @OA\Property(property="updated_by", type="integer", example=1),
 *     @OA\Property(property="updater", type="object", ref="#/components/schemas/User"),
 *     @OA\Property(property="payments", type="array", @OA\Items(ref="#/components/schemas/Payment")),
 *     @OA\Property(property="orderItems", type="array", @OA\Items(ref="#/components/schemas/OrderItem")),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class OrderController extends Controller
{
    /**
     * List Orders.
     *
     * @OA\Get(
     *     path="/api/order-list",
     *     tags={"Order"},
     *     summary="Get paginated list of orders",
     *     @OA\Parameter(name="q", in="query", description="Search by invoice_no or user_id", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", description="Filter by order status", @OA\Schema(type="string")),
     *     @OA\Parameter(name="payment_status", in="query", description="Filter by payment status", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_by", in="query", description="Sort field", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_dir", in="query", description="Sort direction (asc|desc)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Orders retrieved")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => ['nullable','string'],
            'status' => ['nullable','string'],
            'payment_status' => ['nullable','string'],
            'sort_by' => ['nullable','string', Rule::in(['total_price','total_items','order_date','created_at'])],
            'sort_dir' => ['nullable','string', Rule::in(['asc','desc'])],
            'per_page' => ['nullable','integer','min:1','max:200'],
        ]);

        if($validator->fails()){
            return response()->json(['success'=>false,'message'=>'Invalid parameters','errors'=>$validator->errors()],422);
        }

        try {
            $perPage = $request->input('per_page',15);
            $sortBy = $request->input('sort_by','order_date');
            $sortDir = $request->input('sort_dir','desc');

            $query = Order::with(['customer','updater','payments','orderItems']);

            if($q = $request->input('q')){
                $query->where('invoice_no','like',"%{$q}%")
                      ->orWhere('user_id','like',"%{$q}%");
            }

            if($status = $request->input('status')){
                $query->where('status',$status);
            }

            if($paymentStatus = $request->input('payment_status')){
                $query->where('payment_status',$paymentStatus);
            }

            $query->orderBy($sortBy,$sortDir);
            $paginator = $query->paginate($perPage)->appends($request->query());

            return response()->json([
                'success'=>true,
                'message'=>'Orders retrieved',
                'data'=>OrderResource::collection($paginator->items()),
                'meta'=>[
                    'current_page'=>$paginator->currentPage(),
                    'last_page'=>$paginator->lastPage(),
                    'per_page'=>$paginator->perPage(),
                    'total'=>$paginator->total(),
                ],
            ]);

        } catch (\Throwable $e){
            Log::error('Order index failed',['user_id'=>Auth::id(),'ip'=>$request->ip(),'error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to retrieve orders'],500);
        }
    }

    /**
     * Show single Order.
     *
     * @OA\Get(
     *     path="/api/order-show/{id}",
     *     tags={"Order"},
     *     summary="Get single order",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Order retrieved")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $item = Order::with(['customer','updater','payments','orderItems'])->find($id);
        if(!$item){
            return response()->json(['success'=>false,'message'=>'Order not found'],404);
        }
        return response()->json(['success'=>true,'message'=>'Order retrieved','data'=>new OrderResource($item)]);
    }

    /**
     * Create Order.
     *
     * @OA\Post(
     *     path="/api/order-create",
     *     tags={"Order"},
     *     summary="Create new order",
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Order")),
     *     @OA\Response(response=201, description="Order created")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'invoice_no' => ['required','string','min:10','max:100','unique:orders,invoice_no'],
            'order_date' => ['required','date'],
            'user_id' => ['required','integer','exists:customer_details,id'],
            'total_price' => ['required','numeric','min:0'],
            'total_items' => ['required','integer','min:0'],
            'total_qty' => ['required','integer','min:0'],
            'status' => ['nullable','string'],
            'note' => ['nullable','string'],
            'is_inside_dhaka' => ['nullable','boolean'],
            'shipping_cost' => ['required','numeric','min:0'],
            'payment_method' => ['nullable','string'],
            'payment_status' => ['nullable','string'],
            'address' => ['required','string'],
            'updated_by' => ['required','integer','exists:users,id'],
        ];

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);
        }

        DB::beginTransaction();
        try{
            $item = Order::create($request->only(array_keys($rules)));
            DB::commit();
            Log::info('Order created',['user_id'=>Auth::id(),'order_id'=>$item->id]);
            return response()->json(['success'=>true,'message'=>'Order created','data'=>new OrderResource($item->load(['customer','updater','payments','orderItems']))],201);
        } catch (\Throwable $e){
            DB::rollBack();
            Log::error('Order creation failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to create order'],500);
        }
    }

    /**
     * Update Order.
     *
     * @OA\Put(
     *     path="/api/order-update/{id}",
     *     tags={"Order"},
     *     summary="Update order",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Order")),
     *     @OA\Response(response=200, description="Order updated")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $item = Order::find($id);
        if(!$item){
            return response()->json(['success'=>false,'message'=>'Order not found'],404);
        }

        $rules = [
            'invoice_no' => ['sometimes','string','min:10','max:100',Rule::unique('orders','invoice_no')->ignore($id)],
            'order_date' => ['sometimes','date'],
            'user_id' => ['sometimes','integer','exists:customer_details,id'],
            'total_price' => ['sometimes','numeric','min:0'],
            'total_items' => ['sometimes','integer','min:0'],
            'total_qty' => ['sometimes','integer','min:0'],
            'status' => ['sometimes','string'],
            'note' => ['nullable','string'],
            'is_inside_dhaka' => ['sometimes','boolean'],
            'shipping_cost' => ['sometimes','numeric','min:0'],
            'payment_method' => ['sometimes','string'],
            'payment_status' => ['sometimes','string'],
            'address' => ['sometimes','string'],
            'updated_by' => ['sometimes','integer','exists:users,id'],
        ];

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);
        }

        DB::beginTransaction();
        try{
            $item->fill($request->only(array_keys($rules)))->save();
            DB::commit();
            Log::info('Order updated',['user_id'=>Auth::id(),'order_id'=>$item->id]);
            return response()->json(['success'=>true,'message'=>'Order updated','data'=>new OrderResource($item->load(['customer','updater','payments','orderItems']))]);
        } catch (\Throwable $e){
            DB::rollBack();
            Log::error('Order update failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to update order'],500);
        }
    }

    /**
     * Delete Order.
     *
     * @OA\Delete(
     *     path="/api/order-delete/{id}",
     *     tags={"Order"},
     *     summary="Delete order",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Order deleted")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $item = Order::withCount(['payments','orderItems'])->find($id);
        if(!$item){
            return response()->json(['success'=>false,'message'=>'Order not found'],404);
        }

        // Business logic: prevent deletion if payments or order items exist
        if($item->payments_count > 0 || $item->order_items_count > 0){
            return response()->json(['success'=>false,'message'=>'Cannot delete order with payments or order items'],400);
        }

        DB::beginTransaction();
        try{
            $item->delete();
            DB::commit();
            Log::info('Order deleted',['user_id'=>Auth::id(),'order_id'=>$id]);
            return response()->json(['success'=>true,'message'=>'Order deleted']);
        } catch (\Throwable $e){
            DB::rollBack();
            Log::error('Order delete failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to delete order'],500);
        }
    }
}
