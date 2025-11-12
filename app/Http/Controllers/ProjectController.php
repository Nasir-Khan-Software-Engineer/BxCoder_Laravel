<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Project",
 *     description="API endpoints for managing Projects"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Project",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="E-commerce Web App"),
 *     @OA\Property(property="keywords", type="string", example="Laravel, Vue, MySQL"),
 *     @OA\Property(property="short_description", type="string", example="A complete e-commerce solution."),
 *     @OA\Property(property="details", type="string", example="Full project documentation and APIs."),
 *     @OA\Property(property="source_code_link", type="string", example="https://github.com/example/project"),
 *     @OA\Property(property="video_link", type="string", example="https://youtu.be/demo123"),
 *     @OA\Property(property="documentation_link", type="string", example="https://docs.example.com/project"),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="updated_by", type="integer", example=2),
 *     @OA\Property(property="categories", type="array", @OA\Items(type="string", example="Web Development")),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ProjectController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/project-list",
     *     tags={"Project"},
     *     summary="List all projects with pagination, filtering, and sorting",
     *     @OA\Parameter(name="search", in="query", description="Search by title or keywords", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_by", in="query", description="Field to sort by", required=false, @OA\Schema(type="string", example="created_at")),
     *     @OA\Parameter(name="sort_order", in="query", description="Sort order asc/desc", required=false, @OA\Schema(type="string", enum={"asc","desc"}, example="desc")),
     *     @OA\Response(
     *         response=200,
     *         description="Projects retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Projects retrieved successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Project"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Project::with(['creator', 'updater', 'categories']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('keywords', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');

        $projects = $query->orderBy($sortBy, $sortOrder)->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Projects retrieved successfully.',
            'data' => ProjectResource::collection($projects),
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/project-show/{id}",
     *     tags={"Project"},
     *     summary="Show a specific project",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Project retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Project")
     *     ),
     *     @OA\Response(response=404, description="Project not found")
     * )
     */
    public function show($id)
    {
        $project = Project::with(['creator', 'updater', 'categories'])->find($id);

        if (! $project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Project retrieved successfully.',
            'data' => new ProjectResource($project),
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/project-create",
     *     tags={"Project"},
     *     summary="Create a new project",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","keywords","short_description","details","created_by"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="keywords", type="string"),
     *             @OA\Property(property="short_description", type="string"),
     *             @OA\Property(property="details", type="string"),
     *             @OA\Property(property="source_code_link", type="string", nullable=true),
     *             @OA\Property(property="video_link", type="string", nullable=true),
     *             @OA\Property(property="documentation_link", type="string", nullable=true),
     *             @OA\Property(property="category_ids", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="created_by", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Project created successfully"),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:3|unique:projects,title',
            'keywords' => 'required|string|min:3',
            'short_description' => 'required|string|min:3',
            'details' => 'required|string|min:3',
            'source_code_link' => 'nullable|url',
            'video_link' => 'nullable|url',
            'documentation_link' => 'nullable|url',
            'created_by' => 'required|integer|exists:users,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $project = Project::create($request->only([
                'title','keywords','short_description','details',
                'source_code_link','video_link','documentation_link','created_by'
            ]));

            if ($request->has('category_ids')) {
                $project->categories()->sync($request->category_ids);
            }

            DB::commit();

            Log::info('Project created', ['project_id' => $project->id]);

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully.',
                'data' => new ProjectResource($project),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Project creation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Project creation failed.'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/project-update/{id}",
     *     tags={"Project"},
     *     summary="Update an existing project",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="keywords", type="string"),
     *             @OA\Property(property="short_description", type="string"),
     *             @OA\Property(property="details", type="string"),
     *             @OA\Property(property="source_code_link", type="string", nullable=true),
     *             @OA\Property(property="video_link", type="string", nullable=true),
     *             @OA\Property(property="documentation_link", type="string", nullable=true),
     *             @OA\Property(property="category_ids", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="updated_by", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Project updated successfully"),
     *     @OA\Response(response=404, description="Project not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $project = Project::find($id);

        if (! $project) {
            return response()->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|min:3|unique:projects,title,' . $id,
            'keywords' => 'sometimes|required|string|min:3',
            'short_description' => 'sometimes|required|string|min:3',
            'details' => 'sometimes|required|string|min:3',
            'source_code_link' => 'nullable|url',
            'video_link' => 'nullable|url',
            'documentation_link' => 'nullable|url',
            'updated_by' => 'required|integer|exists:users,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $project->update($request->only([
                'title','keywords','short_description','details',
                'source_code_link','video_link','documentation_link','updated_by'
            ]));

            if ($request->has('category_ids')) {
                $project->categories()->sync($request->category_ids);
            }

            DB::commit();

            Log::info('Project updated', ['project_id' => $project->id]);

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully.',
                'data' => new ProjectResource($project),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Project update failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Project update failed.'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/project-delete/{id}",
     *     tags={"Project"},
     *     summary="Delete a project",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Project deleted successfully"),
     *     @OA\Response(response=404, description="Project not found")
     * )
     */
    public function destroy($id)
    {
        $project = Project::find($id);

        if (! $project) {
            return response()->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        try {
            $project->categories()->detach();
            $project->delete();

            Log::info('Project deleted', ['project_id' => $id]);

            return response()->json(['success' => true, 'message' => 'Project deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Project deletion failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Project deletion failed.'], 500);
        }
    }
}
