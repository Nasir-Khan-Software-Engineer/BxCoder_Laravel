<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Payment",
 *     description="API endpoints for managing Payment"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Payment",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=5),
 *     @OA\Property(property="payment_method", type="string", example="card"),
 *     @OA\Property(property="payment_status", type="string", example="pending"),
 *     @OA\Property(property="amount", type="number", format="float", example=150.75),
 *     @OA\Property(property="transaction_id", type="string", example="TXN123456", nullable=true),
 *     @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="updated_by", type="integer", example=2, nullable=true),
 *     @OA\Property(property="order", type="object", ref="#/components/schemas/Order"),
 *     @OA\Property(property="creator", type="object", ref="#/components/schemas/User"),
 *     @OA\Property(property="updater", type="object", ref="#/components/schemas/User"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class PaymentController extends Controller
{
    /**
     * List Payments with pagination, search, filtering, sorting.
     *
     * @OA\Get(
     *     path="/api/payment-list",
     *     tags={"Payment"},
     *     summary="Get paginated list of payments",
     *     @OA\Parameter(name="q", in="query", description="Search by transaction_id or order_id", @OA\Schema(type="string")),
     *     @OA\Parameter(name="payment_status", in="query", description="Filter by status", @OA\Schema(type="string")),
     *     @OA\Parameter(name="payment_method", in="query", description="Filter by payment method", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_by", in="query", description="Sort field (amount,paid_at,created_at)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_dir", in="query", description="Sort direction (asc|desc)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="List retrieved")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => ['nullable','string'],
            'payment_status' => ['nullable','string'],
            'payment_method' => ['nullable','string'],
            'sort_by' => ['nullable','string', Rule::in(['amount','paid_at','created_at'])],
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

            $query = Payment::with(['order','creator','updater']);

            if($q = $request->input('q')){
                $query->where('transaction_id','like',"%{$q}%")
                      ->orWhere('order_id','like',"%{$q}%");
            }

            if($status = $request->input('payment_status')){
                $query->where('payment_status',$status);
            }

            if($method = $request->input('payment_method')){
                $query->where('payment_method',$method);
            }

            $query->orderBy($sortBy,$sortDir);
            $paginator = $query->paginate($perPage)->appends($request->query());

            return response()->json([
                'success'=>true,
                'message'=>'Payments retrieved',
                'data'=>PaymentResource::collection($paginator->items()),
                'meta'=>[
                    'current_page'=>$paginator->currentPage(),
                    'last_page'=>$paginator->lastPage(),
                    'per_page'=>$paginator->perPage(),
                    'total'=>$paginator->total(),
                ],
            ]);

        } catch (\Throwable $e){
            Log::error('Payment index failed',['user_id'=>Auth::id(),'ip'=>$request->ip(),'error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to retrieve payments'],500);
        }
    }

    /**
     * Show single Payment.
     *
     * @OA\Get(
     *     path="/api/payment-show/{id}",
     *     tags={"Payment"},
     *     summary="Get single payment",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Payment retrieved")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $item = Payment::with(['order','creator','updater'])->find($id);
        if(!$item){
            return response()->json(['success'=>false,'message'=>'Payment not found'],404);
        }
        return response()->json(['success'=>true,'message'=>'Payment retrieved','data'=>new PaymentResource($item)]);
    }

    /**
     * Create Payment.
     *
     * @OA\Post(
     *     path="/api/payment-create",
     *     tags={"Payment"},
     *     summary="Create new payment",
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Payment")),
     *     @OA\Response(response=201, description="Payment created")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'order_id' => ['required','integer','exists:orders,id'],
            'payment_method' => ['required','string'],
            'payment_status' => ['nullable','string'],
            'amount' => ['required','numeric','min:0'],
            'transaction_id' => ['nullable','string'],
            'paid_at' => ['nullable','date'],
            'created_by' => ['required','integer','exists:users,id'],
            'updated_by' => ['nullable','integer','exists:users,id'],
        ];

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);
        }

        DB::beginTransaction();
        try{
            $item = Payment::create($request->only(array_keys($rules)));
            DB::commit();
            Log::info('Payment created',['user_id'=>Auth::id(),'payment_id'=>$item->id]);
            return response()->json(['success'=>true,'message'=>'Payment created','data'=>new PaymentResource($item->load(['order','creator','updater']))],201);
        } catch (\Throwable $e){
            DB::rollBack();
            Log::error('Payment creation failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to create payment'],500);
        }
    }

    /**
     * Update Payment.
     *
     * @OA\Put(
     *     path="/api/payment-update/{id}",
     *     tags={"Payment"},
     *     summary="Update payment",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Payment")),
     *     @OA\Response(response=200, description="Payment updated")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $item = Payment::find($id);
        if(!$item){
            return response()->json(['success'=>false,'message'=>'Payment not found'],404);
        }

        $rules = [
            'order_id' => ['sometimes','integer','exists:orders,id'],
            'payment_method' => ['sometimes','string'],
            'payment_status' => ['sometimes','string'],
            'amount' => ['sometimes','numeric','min:0'],
            'transaction_id' => ['nullable','string'],
            'paid_at' => ['nullable','date'],
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
            Log::info('Payment updated',['user_id'=>Auth::id(),'payment_id'=>$item->id]);
            return response()->json(['success'=>true,'message'=>'Payment updated','data'=>new PaymentResource($item->load(['order','creator','updater']))]);
        } catch (\Throwable $e){
            DB::rollBack();
            Log::error('Payment update failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to update payment'],500);
        }
    }

    /**
     * Delete Payment.
     *
     * @OA\Delete(
     *     path="/api/payment-delete/{id}",
     *     tags={"Payment"},
     *     summary="Delete payment",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Payment deleted")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $item = Payment::find($id);
        if(!$item){
            return response()->json(['success'=>false,'message'=>'Payment not found'],404);
        }

        // Business logic: prevent deletion if payment is completed
        if($item->payment_status === 'completed'){
            return response()->json(['success'=>false,'message'=>'Cannot delete completed payment'],400);
        }

        DB::beginTransaction();
        try{
            $item->delete();
            DB::commit();
            Log::info('Payment deleted',['user_id'=>Auth::id(),'payment_id'=>$id]);
            return response()->json(['success'=>true,'message'=>'Payment deleted']);
        } catch (\Throwable $e){
            DB::rollBack();
            Log::error('Payment delete failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to delete payment'],500);
        }
    }
}
