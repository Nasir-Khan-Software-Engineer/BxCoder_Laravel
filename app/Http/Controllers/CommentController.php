<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Comment",
 *     description="API endpoints for managing Comment"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Comment",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="post_id", type="integer", example=10),
 *     @OA\Property(property="user_id", type="integer", example=5),
 *     @OA\Property(property="body", type="string", example="This is a comment."),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="updated_by", type="integer", example=1),
 *     @OA\Property(
 *         property="post",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=10),
 *         @OA\Property(property="title", type="string", example="Post title")
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
class CommentController extends Controller
{
    /**
     * Display a paginated listing of comments.
     *
     * @OA\Get(
     *     path="/api/comment-list",
     *     tags={"Comment"},
     *     summary="List comments with pagination, search, filter, and sorting",
     *     @OA\Parameter(name="search", in="query", description="Search by body", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", description="Filter by status", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="sort_by", in="query", description="Field to sort by", required=false, @OA\Schema(type="string", example="created_at")),
     *     @OA\Parameter(name="sort_order", in="query", description="Sort order", required=false, @OA\Schema(type="string", enum={"asc","desc"}, example="desc")),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comments retrieved successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Comment")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Comment::with(['post', 'user']);

        if ($search = $request->query('search')) {
            $query->where('body', 'like', "%{$search}%");
        }

        if (! is_null($request->query('status'))) {
            $query->where('status', $request->query('status'));
        }

        $sortBy    = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');

        $comments = $query->orderBy($sortBy, $sortOrder)->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Comments retrieved successfully.',
            'data'    => CommentResource::collection($comments),
            'meta'    => [
                'current_page' => $comments->currentPage(),
                'last_page'    => $comments->lastPage(),
                'per_page'     => $comments->perPage(),
                'total'        => $comments->total(),
            ],
        ]);
    }

    /**
     * Display a single comment.
     *
     * @OA\Get(
     *     path="/api/comment-show/{id}",
     *     tags={"Comment"},
     *     summary="Get a single comment",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comment retrieved successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Comment")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function show(int $id)
    {
        $comment = Comment::with(['post', 'user'])->find($id);

        if (! $comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment retrieved successfully.',
            'data'    => new CommentResource($comment),
        ]);
    }

    /**
     * Store a newly created comment.
     *
     * @OA\Post(
     *     path="/api/comment-create",
     *     tags={"Comment"},
     *     summary="Create a new comment",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"post_id","user_id","body"},
     *             @OA\Property(property="post_id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="body", type="string"),
     *             @OA\Property(property="status", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comment created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Comment")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer|exists:posts,id',
            'user_id' => 'required|integer|exists:users,id',
            'body'    => 'required|string|max:5000',
            'status'  => 'boolean',
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

            $comment = Comment::create([
                'post_id'    => $request->post_id,
                'user_id'    => $request->user_id,
                'body'       => $request->body,
                'status'     => $request->status ?? 0,
                'created_by' => auth()->id() ?? null,
                'updated_by' => auth()->id() ?? null,
            ]);

            DB::commit();

            Log::info('Comment created', ['user_id' => auth()->id(), 'comment_id' => $comment->id, 'ip' => $request->ip()]);

            return response()->json([
                'success' => true,
                'message' => 'Comment created successfully.',
                'data'    => new CommentResource($comment),
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Comment creation failed', ['error' => $e->getMessage(), 'user_id' => auth()->id(), 'ip' => $request->ip()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create comment.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified comment.
     *
     * @OA\Put(
     *     path="/api/comment-update/{id}",
     *     tags={"Comment"},
     *     summary="Update an existing comment",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="body", type="string"),
     *             @OA\Property(property="status", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comment updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Comment")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function update(Request $request, int $id)
    {
        $comment = Comment::find($id);

        if (! $comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'body'   => 'sometimes|required|string|max:5000',
            'status' => 'sometimes|boolean',
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

            $comment->update(array_merge(
                $request->only(['body', 'status']),
                ['updated_by' => auth()->id() ?? null]
            ));

            DB::commit();

            Log::info('Comment updated', ['user_id' => auth()->id(), 'comment_id' => $comment->id, 'ip' => $request->ip()]);

            return response()->json([
                'success' => true,
                'message' => 'Comment updated successfully.',
                'data'    => new CommentResource($comment),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Comment update failed', ['error' => $e->getMessage(), 'user_id' => auth()->id(), 'comment_id' => $comment->id, 'ip' => $request->ip()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update comment.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified comment.
     *
     * @OA\Delete(
     *     path="/api/comment-delete/{id}",
     *     tags={"Comment"},
     *     summary="Delete a comment",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Comment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comment deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function destroy(int $id, Request $request)
    {
        $comment = Comment::find($id);

        if (! $comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();

            // Business logic check: Prevent deletion if comment is active
            if ($comment->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete an active comment.',
                ], Response::HTTP_FORBIDDEN);
            }

            $comment->delete();

            DB::commit();

            Log::info('Comment deleted', ['user_id' => auth()->id(), 'comment_id' => $id, 'ip' => $request->ip()]);

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Comment deletion failed', ['error' => $e->getMessage(), 'user_id' => auth()->id(), 'comment_id' => $id, 'ip' => $request->ip()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete comment.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
