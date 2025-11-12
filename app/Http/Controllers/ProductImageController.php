<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductImageResource;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="ProductImage",
 *     description="API endpoints for managing ProductImage"
 * )
 */

/**
 * @OA\Schema(
 *     schema="ProductImage",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="product_id", type="integer", example=10),
 *     @OA\Property(property="image", type="string", example="data:image/png;base64,iVBOR..."),
 *     @OA\Property(property="alt", type="string", example="Front view"),
 *     @OA\Property(property="title", type="string", example="Main Product Image"),
 *     @OA\Property(property="style", type="string", example="thumbnail"),
 *     @OA\Property(property="product", type="object", ref="#/components/schemas/Product"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ProductImageController extends Controller
{
    /**
     * Display a listing of ProductImages.
     *
     * @OA\Get(
     *     path="/api/product-image-list",
     *     tags={"ProductImage"},
     *     summary="Get paginated list of product images",
     *     @OA\Parameter(name="q", in="query", description="Search by title or alt", @OA\Schema(type="string")),
     *     @OA\Parameter(name="product_id", in="query", description="Filter by product ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="sort_by", in="query", description="Sort field (title,created_at)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_dir", in="query", description="Sort direction (asc|desc)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="List of product images")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => ['nullable', 'string'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'sort_by' => ['nullable', 'string', Rule::in(['title', 'created_at'])],
            'sort_dir' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=>false,'message'=>'Invalid query params','errors'=>$validator->errors()],422);
        }

        try {
            $perPage = $request->input('per_page',15);
            $sortBy = $request->input('sort_by','created_at');
            $sortDir = $request->input('sort_dir','desc');

            $query = ProductImage::with('product');

            if ($search = $request->input('q')) {
                $query->where(fn($q)=>$q->where('title','like',"%{$search}%")
                                        ->orWhere('alt','like',"%{$search}%"));
            }

            if ($pid = $request->input('product_id')) {
                $query->where('product_id',$pid);
            }

            $query->orderBy($sortBy,$sortDir);
            $paginator = $query->paginate($perPage)->appends($request->query());

            return response()->json([
                'success'=>true,
                'message'=>'Product images retrieved',
                'data'=>ProductImageResource::collection($paginator->items()),
                'meta'=>[
                    'current_page'=>$paginator->currentPage(),
                    'last_page'=>$paginator->lastPage(),
                    'per_page'=>$paginator->perPage(),
                    'total'=>$paginator->total(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('ProductImage index failed',['user_id'=>Auth::id(),'ip'=>$request->ip(),'error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to load product images'],500);
        }
    }

    /**
     * Show a ProductImage.
     *
     * @OA\Get(
     *     path="/api/product-image-show/{id}",
     *     tags={"ProductImage"},
     *     summary="Get single product image",
     *     @OA\Parameter(name="id",in="path",required=true,@OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Product image retrieved")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $item = ProductImage::with('product')->find($id);
        if (! $item) {
            return response()->json(['success'=>false,'message'=>'Product image not found'],404);
        }
        return response()->json(['success'=>true,'message'=>'Product image retrieved','data'=>new ProductImageResource($item)]);
    }

    /**
     * Create ProductImage.
     *
     * @OA\Post(
     *     path="/api/product-image-create",
     *     tags={"ProductImage"},
     *     summary="Create new product image",
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/ProductImage")),
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'product_id' => ['required','integer','exists:products,id'],
            'image' => ['required','string'],
            'alt' => ['required','string','min:3'],
            'title' => ['required','string','min:3'],
            'style' => ['nullable','string','min:3'],
        ];
        $validator = Validator::make($request->all(),$rules);
        if ($validator->fails()) {
            return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);
        }

        DB::beginTransaction();
        try {
            $item = ProductImage::create($request->only(array_keys($rules)));
            DB::commit();
            Log::info('ProductImage created',['user_id'=>Auth::id(),'id'=>$item->id]);
            return response()->json(['success'=>true,'message'=>'Product image created','data'=>new ProductImageResource($item->load('product'))],201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ProductImage create failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to create'],500);
        }
    }

    /**
     * Update ProductImage.
     *
     * @OA\Put(
     *     path="/api/product-image-update/{id}",
     *     tags={"ProductImage"},
     *     summary="Update product image",
     *     @OA\Parameter(name="id",in="path",required=true,@OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/ProductImage")),
     *     @OA\Response(response=200, description="Updated")
     * )
     */
    public function update(Request $request,int $id): JsonResponse
    {
        $item = ProductImage::find($id);
        if(!$item){
            return response()->json(['success'=>false,'message'=>'Not found'],404);
        }

        $rules = [
            'product_id' => ['sometimes','integer','exists:products,id'],
            'image' => ['sometimes','string'],
            'alt' => ['sometimes','string','min:3'],
            'title' => ['sometimes','string','min:3'],
            'style' => ['nullable','string','min:3'],
        ];

        $validator = Validator::make($request->all(),$rules);
        if ($validator->fails()) {
            return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);
        }

        DB::beginTransaction();
        try {
            $item->fill($request->only(array_keys($rules)))->save();
            DB::commit();
            Log::info('ProductImage updated',['user_id'=>Auth::id(),'id'=>$item->id]);
            return response()->json(['success'=>true,'message'=>'Product image updated','data'=>new ProductImageResource($item->load('product'))]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ProductImage update failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to update'],500);
        }
    }

    /**
     * Delete ProductImage.
     *
     * @OA\Delete(
     *     path="/api/product-image-delete/{id}",
     *     tags={"ProductImage"},
     *     summary="Delete product image",
     *     @OA\Parameter(name="id",in="path",required=true,@OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $item = ProductImage::find($id);
        if(!$item){
            return response()->json(['success'=>false,'message'=>'Not found'],404);
        }

        DB::beginTransaction();
        try {
            $item->delete();
            DB::commit();
            Log::info('ProductImage deleted',['user_id'=>Auth::id(),'id'=>$id]);
            return response()->json(['success'=>true,'message'=>'Product image deleted']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ProductImage delete failed',['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to delete'],500);
        }
    }
}
