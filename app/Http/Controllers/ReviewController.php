<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Review",
 *     description="API endpoints for managing Review"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Review",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="product_id", type="integer", example=10),
 *     @OA\Property(property="user_id", type="integer", example=5),
 *     @OA\Property(property="rating", type="integer", example=4),
 *     @OA\Property(property="comment", type="string", example="Great product!"),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(
 *         property="product",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=10),
 *         @OA\Property(property="name", type="string", example="Product Name")
 *     ),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=5),
 *         @OA\Property(property="name", type="string", example="John Doe")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ReviewController extends Controller
{
    /**
     * Display a paginated listing of reviews.
     *
     * @OA\Get(
     *     path="/api/review-list",
     *     tags={"Review"},
     *     summary="List reviews with pagination, search, filter, and sorting",
     *     @OA\Parameter(name="search", in="query", description="Search by comment", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", description="Filter by status", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="sort_by", in="query", description="Field to sort by", required=false, @OA\Schema(type="string", example="created_at")),
     *     @OA\Parameter(name="sort_order", in="query", description="Sort order", required=false, @OA\Schema(type="string", enum={"asc","desc"}, example="desc")),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reviews retrieved successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Review")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Review::with(['product', 'user']);

        if ($search = $request->query('search')) {
            $query->where('comment', 'like', "%{$search}%");
        }

        if (!is_null($request->query('status'))) {
            $query->where('status', $request->query('status'));
        }

        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');

        $reviews = $query->orderBy($sortBy, $sortOrder)->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Reviews retrieved successfully.',
            'data' => ReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ]
        ]);
    }

    /**
     * Display a single review.
     *
     * @OA\Get(
     *     path="/api/review-show/{id}",
     *     tags={"Review"},
     *     summary="Get a single review",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Review retrieved successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Review not found")
     * )
     */
    public function show(int $id)
    {
        $review = Review::with(['product', 'user'])->find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Review retrieved successfully.',
            'data' => new ReviewResource($review)
        ]);
    }

    /**
     * Store a newly created review.
     *
     * @OA\Post(
     *     path="/api/review-create",
     *     tags={"Review"},
     *     summary="Create a new review",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id","user_id","rating","comment"},
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="rating", type="integer"),
     *             @OA\Property(property="comment", type="string"),
     *             @OA\Property(property="status", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Review created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Review created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Review")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'user_id' => 'required|integer|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:5000',
            'status' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $review = Review::create([
                'product_id' => $request->product_id,
                'user_id' => $request->user_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => $request->status ?? 0,
            ]);

            DB::commit();

            Log::info('Review created', ['review_id' => $review->id, 'ip' => $request->ip()]);

            return response()->json([
                'success' => true,
                'message' => 'Review created successfully.',
                'data' => new ReviewResource($review)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Review creation failed', ['error' => $e->getMessage(), 'ip' => $request->ip()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create review.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified review.
     *
     * @OA\Put(
     *     path="/api/review-update/{id}",
     *     tags={"Review"},
     *     summary="Update an existing review",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="rating", type="integer"),
     *             @OA\Property(property="comment", type="string"),
     *             @OA\Property(property="status", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Review updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Review not found")
     * )
     */
    public function update(Request $request, int $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'sometimes|required|string|max:5000',
            'status' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $review->update($request->only(['rating', 'comment', 'status']));

            DB::commit();

            Log::info('Review updated', ['review_id' => $review->id, 'ip' => $request->ip()]);

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully.',
                'data' => new ReviewResource($review)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Review update failed', ['error' => $e->getMessage(), 'review_id' => $review->id, 'ip' => $request->ip()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update review.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified review.
     *
     * @OA\Delete(
     *     path="/api/review-delete/{id}",
     *     tags={"Review"},
     *     summary="Delete a review",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Review deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Review deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Review not found")
     * )
     */
    public function destroy(int $id, Request $request)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();

            // Business logic: Prevent deletion if review is active
            if ($review->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete an active review.',
                ], Response::HTTP_FORBIDDEN);
            }

            $review->delete();

            DB::commit();

            Log::info('Review deleted', ['review_id' => $id, 'ip' => $request->ip()]);

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Review deletion failed', ['error' => $e->getMessage(), 'review_id' => $id, 'ip' => $request->ip()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
