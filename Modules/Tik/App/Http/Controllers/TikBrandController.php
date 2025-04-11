<?php

namespace Modules\Tik\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Tik\Repositories\TikBrand\TikBrandInterface;

class TikBrandController extends Controller
{
    protected $tikBrandRepository;

    const errMess = 'Không tìm thấy thương hiệu';
    const errSystem = 'Có lỗi xảy ra';
    const errCreate = 'Tạo thương hiệu không thành công';
    const errUpdate = 'Cập nhật thương hiệu không thành công';
    public function __construct(TikBrandInterface $tikBrandRepository)
    {
        $this->tikBrandRepository = $tikBrandRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/tik-brand",
     *     summary="Danh sách thương hiệu",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Lọc theo tên thương hiệu",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách thương hiệu",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TikBrand"))
     *     ),
     *     @OA\Response(response=400, description="Request không hợp lệ")
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $brands = $this->tikBrandRepository->getAll($queryData);
        return Result::success($brands);
    }

    /**
     * @OA\Get(
     *     path="/api/tik-brand/{id}",
     *     summary="Lấy thông tin thương hiệu",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID thương hiệu"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin thương hiệu",
     *         @OA\JsonContent(ref="#/components/schemas/TikBrand")
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy thương hiệu")
     * )
     */
    public function show($id)
    {
        $brand = $this->tikBrandRepository->findById($id);
        if (!$brand) {
            return Result::fail(self::errMess);
        }
        return Result::success($brand);
    }

    /**
     * @OA\Post(
     *     path="/api/tik-brand",
     *     summary="Thêm thương hiệu",
     *     tags={"Tik"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "authorized_status", "is_t1_brand"},
     *             @OA\Property(property="name", type="string", example="Nike"),
     *             @OA\Property(property="authorized_status", type="integer", example=2),
     *             @OA\Property(property="is_t1_brand", type="boolean", example=true),
     *             @OA\Property(property="created_by", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thương hiệu được tạo thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TikBrand")
     *     ),
     *     @OA\Response(response=400, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=500, description="Lỗi tạo thương hiệu")
     * )
     */

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $brand = $this->tikBrandRepository->create($data);
            if (!$brand) {
                return Result::fail(self::errCreate);
            }
            return Result::success($brand);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tik-brand/{id}",
     *     summary="Cập nhật thương hiệu",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID thương hiệu"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "authorized_status", "is_t1_brand"},
     *             @OA\Property(property="name", type="string", example="Adidas"),
     *             @OA\Property(property="authorized_status", type="integer", example=1),
     *             @OA\Property(property="is_t1_brand", type="boolean", example=false),
     *             @OA\Property(property="updated_by", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thương hiệu được cập nhật thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TikBrand")
     *     ),
     *     @OA\Response(response=400, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=404, description="Không tìm thấy thương hiệu"),
     *     @OA\Response(response=500, description="Lỗi cập nhật thương hiệu")
     * )
     */

    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $brand = $this->tikBrandRepository->update($id, $data);
            if (!$brand) {
                return Result::fail(self::errUpdate);
            }
            return Result::success($brand);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tik-brand/{id}",
     *     summary="Xóa thương hiệu",
     *     description="Xóa thương hiệu theo ID",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id thương hiệu cần xóa",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand successfully deleted.",
     *         @OA\JsonContent(ref="#/components/schemas/TikBrand")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Brand not found.",
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        $brand = $this->tikBrandRepository->delete($id);
        if (!$brand) {
            return Result::fail(self::errMess);
        }
        return Result::success($brand);
    }
}
