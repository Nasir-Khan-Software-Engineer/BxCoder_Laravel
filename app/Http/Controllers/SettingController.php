<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Setting",
 *     description="API endpoints for managing Setting"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Setting",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="My Company"),
 *     @OA\Property(property="email", type="string", example="info@company.com"),
 *     @OA\Property(property="phone", type="string", example="01234567890"),
 *     @OA\Property(property="address", type="string", example="123 Main Street"),
 *     @OA\Property(property="logo", type="string", example="logo.png"),
 *     @OA\Property(property="favicon", type="string", example="favicon.ico"),
 *     @OA\Property(property="meta_title", type="string", example="Company Meta Title"),
 *     @OA\Property(property="meta_keywords", type="string", example="keyword1, keyword2"),
 *     @OA\Property(property="meta_description", type="string", example="Meta description"),
 *     @OA\Property(property="copyright", type="string", example="© 2025 Company"),
 *     @OA\Property(property="facebook", type="string", example="https://facebook.com/company"),
 *     @OA\Property(property="twitter", type="string", example="https://twitter.com/company"),
 *     @OA\Property(property="instagram", type="string", example="https://instagram.com/company"),
 *     @OA\Property(property="linkedin", type="string", example="https://linkedin.com/company"),
 *     @OA\Property(property="youtube", type="string", example="https://youtube.com/company"),
 *     @OA\Property(property="tiktok", type="string", example="https://tiktok.com/@company"),
 *     @OA\Property(property="product_prefix", type="string", example="PRD"),
 *     @OA\Property(property="order_prefix", type="string", example="ORD"),
 *     @OA\Property(property="invoice_prefix", type="string", example="INV"),
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
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SettingController extends Controller
{
    /**
     * Display a paginated listing of settings.
     *
     * @OA\Get(
     *     path="/api/setting-list",
     *     tags={"Setting"},
     *     summary="List settings with pagination, search, filter, and sorting",
     *     @OA\Parameter(name="search", in="query", description="Search by name or email", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_by", in="query", description="Field to sort by", required=false, @OA\Schema(type="string", example="created_at")),
     *     @OA\Parameter(name="sort_order", in="query", description="Sort order", required=false, @OA\Schema(type="string", enum={"asc","desc"}, example="desc")),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Settings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Settings retrieved successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Setting")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Setting::with(['creator', 'updater']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!is_null($request->query('status'))) {
            // Some projects store a 'status' on settings - keep this flexible (not in model fillable but allow filter if exists)
            $query->where('status', $request->query('status'));
        }

        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');

        $settings = $query->orderBy($sortBy, $sortOrder)->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Settings retrieved successfully.',
            'data' => SettingResource::collection($settings),
            'meta' => [
                'current_page' => $settings->currentPage(),
                'last_page' => $settings->lastPage(),
                'per_page' => $settings->perPage(),
                'total' => $settings->total(),
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified setting.
     *
     * @OA\Get(
     *     path="/api/setting-show/{id}",
     *     tags={"Setting"},
     *     summary="Get a single setting",
     *     @OA\Parameter(name="id", in="path", description="Setting ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Setting retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Setting retrieved successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Setting")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Setting not found")
     * )
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        $setting = Setting::with(['creator', 'updater'])->find($id);

        if (! $setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Setting retrieved successfully.',
            'data' => new SettingResource($setting),
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created setting.
     *
     * @OA\Post(
     *     path="/api/setting-create",
     *     tags={"Setting"},
     *     summary="Create a new setting",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={
     *                 "name","email","phone","address",
     *                 "logo","favicon",
     *                 "meta_title","meta_keywords","meta_description",
     *                 "copyright",
     *                 "product_prefix","order_prefix","invoice_prefix",
     *                 "created_by"
     *             },
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="logo", type="string", description="File path or base64 string"),
     *             @OA\Property(property="favicon", type="string", description="File path or base64 string"),
     *             @OA\Property(property="meta_title", type="string"),
     *             @OA\Property(property="meta_keywords", type="string"),
     *             @OA\Property(property="meta_description", type="string"),
     *             @OA\Property(property="copyright", type="string"),
     *             @OA\Property(property="facebook", type="string", nullable=true),
     *             @OA\Property(property="twitter", type="string", nullable=true),
     *             @OA\Property(property="instagram", type="string", nullable=true),
     *             @OA\Property(property="linkedin", type="string", nullable=true),
     *             @OA\Property(property="youtube", type="string", nullable=true),
     *             @OA\Property(property="tiktok", type="string", nullable=true),
     *             @OA\Property(property="product_prefix", type="string"),
     *             @OA\Property(property="order_prefix", type="string"),
     *             @OA\Property(property="invoice_prefix", type="string"),
     *             @OA\Property(property="created_by", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Setting created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Setting created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Setting")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:1000',
            'email' => 'required|string|email|min:3|max:200',
            'phone' => 'required|string|size:11',
            'address' => 'required|string|min:3|max:1000',
            'logo' => 'required|string',
            'favicon' => 'required|string',
            'meta_title' => 'required|string|min:3|max:200',
            'meta_keywords' => 'required|string|min:3|max:1000',
            'meta_description' => 'required|string|min:3|max:1000',
            'copyright' => 'required|string|min:3|max:200',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|url',
            'instagram' => 'nullable|url',
            'linkedin' => 'nullable|url',
            'youtube' => 'nullable|url',
            'tiktok' => 'nullable|url',
            'product_prefix' => 'required|string|min:3|max:10',
            'order_prefix' => 'required|string|min:3|max:10',
            'invoice_prefix' => 'required|string|min:3|max:10',
            'created_by' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $data = $request->only([
                'name','email','phone','address',
                'logo','favicon',
                'meta_title','meta_keywords','meta_description',
                'copyright',
                'facebook','twitter','instagram','linkedin','youtube','tiktok',
                'product_prefix','order_prefix','invoice_prefix',
                'created_by'
            ]);

            // set updated_by same as created_by if not provided
            $data['updated_by'] = $request->input('updated_by', $request->created_by);

            $setting = Setting::create($data);

            DB::commit();

            Log::info('Setting created', [
                'setting_id' => $setting->id,
                'user_id' => $request->created_by,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Setting created successfully.',
                'data' => new SettingResource($setting),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Setting creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->created_by ?? null,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create setting.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified setting.
     *
     * @OA\Put(
     *     path="/api/setting-update/{id}",
     *     tags={"Setting"},
     *     summary="Update an existing setting",
     *     @OA\Parameter(name="id", in="path", description="Setting ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="logo", type="string"),
     *             @OA\Property(property="favicon", type="string"),
     *             @OA\Property(property="meta_title", type="string"),
     *             @OA\Property(property="meta_keywords", type="string"),
     *             @OA\Property(property="meta_description", type="string"),
     *             @OA\Property(property="copyright", type="string"),
     *             @OA\Property(property="facebook", type="string", nullable=true),
     *             @OA\Property(property="twitter", type="string", nullable=true),
     *             @OA\Property(property="instagram", type="string", nullable=true),
     *             @OA\Property(property="linkedin", type="string", nullable=true),
     *             @OA\Property(property="youtube", type="string", nullable=true),
     *             @OA\Property(property="tiktok", type="string", nullable=true),
     *             @OA\Property(property="product_prefix", type="string"),
     *             @OA\Property(property="order_prefix", type="string"),
     *             @OA\Property(property="invoice_prefix", type="string"),
     *             @OA\Property(property="updated_by", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Setting updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Setting updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Setting")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Setting not found"),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $setting = Setting::find($id);

        if (! $setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|min:3|max:1000',
            'email' => 'sometimes|required|string|email|min:3|max:200',
            'phone' => 'sometimes|required|string|size:11',
            'address' => 'sometimes|required|string|min:3|max:1000',
            'logo' => 'sometimes|required|string',
            'favicon' => 'sometimes|required|string',
            'meta_title' => 'sometimes|required|string|min:3|max:200',
            'meta_keywords' => 'sometimes|required|string|min:3|max:1000',
            'meta_description' => 'sometimes|required|string|min:3|max:1000',
            'copyright' => 'sometimes|required|string|min:3|max:200',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|url',
            'instagram' => 'nullable|url',
            'linkedin' => 'nullable|url',
            'youtube' => 'nullable|url',
            'tiktok' => 'nullable|url',
            'product_prefix' => 'sometimes|required|string|min:3|max:10',
            'order_prefix' => 'sometimes|required|string|min:3|max:10',
            'invoice_prefix' => 'sometimes|required|string|min:3|max:10',
            'updated_by' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $allowed = [
                'name','email','phone','address',
                'logo','favicon',
                'meta_title','meta_keywords','meta_description',
                'copyright',
                'facebook','twitter','instagram','linkedin','youtube','tiktok',
                'product_prefix','order_prefix','invoice_prefix',
                'updated_by'
            ];

            $setting->update($request->only($allowed));

            DB::commit();

            Log::info('Setting updated', [
                'setting_id' => $setting->id,
                'user_id' => $request->updated_by,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully.',
                'data' => new SettingResource($setting),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Setting update failed', [
                'error' => $e->getMessage(),
                'setting_id' => $setting->id,
                'user_id' => $request->updated_by ?? null,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update setting.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified setting.
     *
     * @OA\Delete(
     *     path="/api/setting-delete/{id}",
     *     tags={"Setting"},
     *     summary="Delete a setting",
     *     @OA\Parameter(name="id", in="path", description="Setting ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Setting deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Setting deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Setting not found"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id, Request $request)
    {
        $setting = Setting::find($id);

        if (! $setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();

            // Business logic checks: example — prevent deletion if this setting is the only global config
            // Adjust this logic to your app's needs. Here we allow deletion with no extra checks.
            $setting->delete();

            DB::commit();

            Log::info('Setting deleted', [
                'setting_id' => $id,
                'user_id' => auth()->id() ?? null,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Setting deleted successfully.',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Setting deletion failed', [
                'error' => $e->getMessage(),
                'setting_id' => $id,
                'user_id' => auth()->id() ?? null,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete setting.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
