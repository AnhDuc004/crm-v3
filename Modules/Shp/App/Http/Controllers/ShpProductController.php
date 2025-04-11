<?php

namespace Modules\Shp\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Modules\Shp\Repositories\ShpProduct\ShpProductInterface;

class ShpProductController extends Controller
{
    protected $shpProductRepository;

    const errMess = 'Sản phẩm không tồn tại';
    const errUpdate = 'Cập nhật thất bại';
    const errCreate = 'Thêm thất bại';
    const errDelete = 'Xóa thất bại';
    const errSystem = 'Lỗi hệ thống';


    public function __construct(ShpProductInterface $shpProductInterface)
    {
        $this->shpProductRepository = $shpProductInterface;
    }
    /**
     * @OA\Get(
     *     path="/api/shp-product",
     *     summary="Lấy danh sách sản phẩm có phân trang và tìm kiếm",
     *     tags={"Shp"},
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Số lượng sản phẩm mỗi trang (mặc định 10)",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Trang hiện tại (mặc định 1)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=false,
     *         description="Tìm kiếm theo tên sản phẩm",
     *         @OA\Schema(type="string", example="Nike")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách sản phẩm",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/ShpProduct")
     *             ),
     *             @OA\Property(property="total", type="integer", example=50),
     *             @OA\Property(property="per_page", type="integer", example=10),
     *             @OA\Property(property="last_page", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(response=500, description="Lỗi máy chủ")
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->all();
        $products = $this->shpProductRepository->getAll($perPage);

        return Result::success($products);
    }

    /**
     * @OA\Get(
     *     path="/api/shp-product/{id}",
     *     summary="Lấy thông tin chi tiết sản phẩm",
     *     tags={"Shp"},
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của sản phẩm cần lấy thông tin",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin chi tiết sản phẩm",
     *         @OA\JsonContent(ref="#/components/schemas/ShpProduct")
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy sản phẩm"),
     *     @OA\Response(response=500, description="Lỗi máy chủ")
     * )
     */
    public function show($id)
    {
        $product = $this->shpProductRepository->findById($id);

        if (!$product) {
            return Result::fail(self::errMess);
        }

        return Result::success($product);
    }


    /**
     * @OA\Post(
     *     path="/api/shp-product",
     *     summary="Tạo mới sản phẩm",
     *     tags={"Shp"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ShpProduct")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sản phẩm được tạo thành công",
     *         @OA\JsonContent(ref="#/components/schemas/ShpProduct")
     *     ),
     *     @OA\Response(response=400, description="Dữ liệu không hợp lệ"),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        try {
            $product = $this->shpProductRepository->create($request->all());
            if (!$product) {
                return Result::fail(self::errCreate);
            }
            return Result::success($product);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/shp-product/{id}",
     *     summary="Cập nhật sản phẩm",
     *     tags={"Shp"},
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID sản phẩm cần cập nhật",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ShpProduct")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sản phẩm đã được cập nhật thành công",
     *         @OA\JsonContent(ref="#/components/schemas/ShpProduct")
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy sản phẩm"),
     *     @OA\Response(response=400, description="Lỗi đầu vào"),
     *     @OA\Response(response=500, description="Lỗi máy chủ")
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $product = $this->shpProductRepository->update($id, $data);
            return Result::success($product);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/shp-product/{id}",
     *     summary="Xóa sản phẩm",
     *     tags={"Shp"},
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của sản phẩm cần xóa",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sản phẩm đã được xóa thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sản phẩm đã được xóa thành công")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy sản phẩm"),
     *     @OA\Response(response=500, description="Lỗi máy chủ")
     * )
     */
    public function destroy($id)
    {
        $deleted = $this->shpProductRepository->delete($id);

        if (!$deleted) {
            return Result::fail(self::errDelete);
        }
        return Result::success($deleted);
    }
}
