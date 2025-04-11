<?php

namespace Modules\Shp\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Shp\Repositories\ShpCategory\ShpCategoryInterface;

class ShpCategoryController extends Controller
{
    protected $shpCategoryRepository;

    const messageCreate = 'Tạo thất bại';
    const messageUpdate = 'Cập nhật thất bại';
    const messageDelete = 'Xóa thất bại';
    const messageError = 'Không tìm thấy';

    public function __construct(ShpCategoryInterface $shpCategoryRepository)
    {
        $this->shpCategoryRepository = $shpCategoryRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/shp-category",
     *     summary="Lấy danh sách thương hiệu",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Số lượng bản ghi trên mỗi trang (mặc định: 10)",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Trang cần lấy dữ liệu (mặc định: 1)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Tên thương hiệu cần tìm kiếm",
     *         @OA\Schema(type="string", example="Giày")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy danh sách thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ShpCategory")),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="total_pages", type="integer", example=5),
     *                 @OA\Property(property="total_items", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Lỗi máy chủ")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $data = $request->all();
        $categories = $this->shpCategoryRepository->getAll($data);
        return Result::success($categories);
    }

    /**
     * @OA\Get(
     *     path="/api/shp-category/{id}",
     *     summary="Lấy thông tin chi tiết danh mục",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID danh mục",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin danh mục",
     *         @OA\JsonContent(ref="#/components/schemas/ShpCategory")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy danh mục"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $category = $this->shpCategoryRepository->findById($id);
            if (!$category) {
                return Result::fail(self::messageError);
            }
            return Result::success($category);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageError);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/shp-category",
     *     summary="Tạo danh mục sản phẩm mới",
     *     description="API này dùng để tạo mới một danh mục sản phẩm trong hệ thống.",
     *     tags={"Shp"},
     *     security={{ "bearer":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_name"},
     *             @OA\Property(property="shp_id", type="integer", nullable=true, example=1001, description="ID danh mục từ hệ thống SHP"),
     *             @OA\Property(property="category_name", type="string", maxLength=255, example="Giày thể thao", description="Tên danh mục sản phẩm"),
     *             @OA\Property(property="parent_category_id", type="integer", nullable=true, example=2, description="ID danh mục cha (nếu có)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ShpCategory")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Lỗi tạo danh mục")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'shp_id' => 'nullable|integer',
            'category_name' => 'required|string|max:255',
            'parent_category_id' => 'nullable|integer',
        ]);
        try {
            $category = $this->shpCategoryRepository->create($data);
            return Result::success($category);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageCreate);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/shp-category/{id}",
     *     summary="Cập nhật danh mục sản phẩm",
     *     description="API này dùng để cập nhật thông tin danh mục sản phẩm trong hệ thống.",
     *     tags={"Shp"},
     *     security={{ "bearer":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của danh mục cần cập nhật",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ShpCategory")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ShpCategory")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy danh mục",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Danh mục không tồn tại")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Lỗi cập nhật danh mục")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'shp_id' => 'nullable|integer',
            'category_name' => 'required|string|max:255',
            'parent_category_id' => 'nullable|integer',
        ]);
        try {
            $category = $this->shpCategoryRepository->update($id, $data);
            return Result::success($category);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageUpdate);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/shp-category/{id}",
     *     summary="Xóa danh mục sản phẩm",
     *     description="API này dùng để xóa một danh mục sản phẩm trong hệ thống.",
     *     tags={"Shp"},
     *     security={{ "bearer":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của danh mục cần xóa",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Danh mục đã được xóa thành công")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy danh mục",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Danh mục không tồn tại")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Lỗi khi xóa danh mục")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $data = $this->shpCategoryRepository->delete($id);
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageDelete);
        }
    }
}
