<?php

namespace Modules\Tik\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tik\Repositories\TikCategory\TikCategoryInterface;

class TikCategoryController extends Controller
{
    protected $tikCategoryRepository;

    const errMess = 'Không tìm thấy danh mục';
    const errMessCreate = 'Tạo danh mục thất bại';
    const errMessUpdate = 'Cập nhật danh mục thất bại';
    const errMessDelete = 'Xóa danh mục thất bại';
    const errSystem = 'Lỗi hệ thống';
    public function __construct(TikCategoryInterface $tikCategoryRepository)
    {
        $this->tikCategoryRepository = $tikCategoryRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/tik-category",
     *     security={{"bearer":{}}},
     *     summary="Danh sách danh mục",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="local_display_name",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Lọc theo tên hiển thị của danh mục"
     *     ),
     *     @OA\Parameter(
     *         name="is_leaf",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="boolean"),
     *         description="Lọc theo xem danh mục có phải là danh mục lá (true/false)"
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", example="{'active': true}"),
     *         description="Lọc theo trạng thái của danh mục dưới dạng JSON"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách danh mục sau khi lọc",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TikCategory"))
     *     ),
     *     @OA\Response(response=400, description="Request không hợp lệ")
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $categories = $this->tikCategoryRepository->getAll($queryData);
        return Result::success($categories);
    }

    /**
     * @OA\Get(
     *     path="/api/tik-category/{id}",
     *     security={{"bearer":{}}},
     *     summary="Lấy thông tin danh mục",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID danh mục"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin danh mục",
     *         @OA\JsonContent(ref="#/components/schemas/TikCategory")
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy danh mục")
     * )
     */
    public function show($id)
    {
        $category = $this->tikCategoryRepository->findById($id);
        if (!$category) {
            return Result::fail(self::errMess);
        }
        return Result::success($category);
    }

    /**
     * @OA\Post(
     *     path="/api/tik-category",
     *     security={{"bearer":{}}},
     *     summary="Tạo danh mục",
     *     tags={"Tik"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"local_display_name", "is_leaf", "status"},
     *             @OA\Property(property="local_display_name", type="string", example="Electronics"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
     *             @OA\Property(property="is_leaf", type="boolean", example=false),
     *             @OA\Property(property="status", type="object", example={"active": true}),
     *             @OA\Property(property="created_by", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh mục được tạo thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TikCategory")
     *     ),
     *     @OA\Response(response=400, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=500, description="Lỗi tạo danh mục")
     * )
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $category = $this->tikCategoryRepository->create($data);
            if (!$category) {
                return Result::fail(self::errMessCreate);
            }
            return Result::success($category);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tik-category/{id}",
     *     security={{"bearer":{}}},
     *     summary="Update a category",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID danh mục"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"local_display_name", "is_leaf", "status"},
     *             @OA\Property(property="local_display_name", type="string", example="Computers"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="is_leaf", type="boolean", example=true),
     *             @OA\Property(property="status", type="object", example={"active": false}),
     *             @OA\Property(property="updated_by", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh mục được cập nhật thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TikCategory")
     *     ),
     *     @OA\Response(response=400, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=404, description="Không tìm thấy danh mục"),
     *     @OA\Response(response=500, description="Lỗi cập nhật danh mục")
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $category = $this->tikCategoryRepository->update($id, $data);
            if (!$category) {
                return Result::fail(self::errMessUpdate);
            }
            return Result::success($category);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tik-category/{id}",
     *     summary="Xóa danh mục",
     *     description="Xóa danh mục theo id",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id danh mục cần xóa",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category successfully deleted.",
     *         @OA\JsonContent(ref="#/components/schemas/TikCategory")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found.",
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $category = $this->tikCategoryRepository->delete($id);
            if (!$category) {
                return Result::fail(self::errMessDelete);
            }
            return Result::success($category);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }
}
