<?php

namespace Modules\Tik\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tik\Repositories\TikProduct\TikProductInterface;

class TikProductController extends Controller
{
    protected $tikProductRepository;

    const errMess = 'Không tìm thấy sản phẩm';
    const errCreate = 'Tạo sản phẩm thất bại';
    const errUpdate = 'Cập nhật sản phẩm thất bại';
    const errDelete = 'Xóa sản phẩm thất bại';
    public function __construct(TikProductInterface $tikProductRepository)
    {
        $this->tikProductRepository = $tikProductRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/tik-product",
     *     summary="Danh sách sản phẩm",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Lọc theo tên sản phẩm",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách sản phẩm",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TikProduct"))
     *     ),
     *     @OA\Response(response=400, description="Request không hợp lệ")
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $products = $this->tikProductRepository->getAll($queryData);
        return Result::success($products);
    }

    /**
     * @OA\Get(
     *     path="/api/tik-product/{id}",
     *     summary="Lấy thông tin chi tiết sản phẩm",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin sản phẩm",
     *         @OA\JsonContent(ref="#/components/schemas/TikProduct")
     *     ),
     *     @OA\Response(response=404, description="Sản phẩm không tìm thấy")
     * )
     */
    public function show($id)
    {
        $product = $this->tikProductRepository->findById($id);
        if (!$product) {
            return Result::fail(self::errMess);
        }
        return Result::success($product);
    }

    /**
     * @OA\Post(
     *     path="/api/tik-product",
     *     summary="Tạo mới sản phẩm",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "product_id", "status", "total"},
     *             @OA\Property(property="name", type="string", example="iPhone 13 Pro"),
     *             @OA\Property(property="product_id", type="integer", example=12345),
     *             @OA\Property(property="status", type="integer", example=4),
     *             @OA\Property(property="total", type="integer", example=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo sản phẩm thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TikProduct")
     *     ),
     *     @OA\Response(response=400, description="Request không hợp lệ")
     * )
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $product = $this->tikProductRepository->create($data);
            if (!$product) {
                return Result::fail(self::errCreate);
            }
            return Result::success($product);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tik-product/{id}",
     *     summary="Cập nhật thông tin sản phẩm",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "product_id", "status", "total"},
     *             @OA\Property(property="name", type="string", example="iPhone 13 Pro"),
     *             @OA\Property(property="product_id", type="integer", example=12345),
     *             @OA\Property(property="status", type="integer", example=4),
     *             @OA\Property(property="total", type="integer", example=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật sản phẩm thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TikProduct")
     *     ),
     *     @OA\Response(response=404, description="Sản phẩm không tìm thấy")
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $product = $this->tikProductRepository->update($id, $data);
            if (!$product) {
                return Result::fail(self::errUpdate);
            }
            return Result::success($product);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tik-product/{id}",
     *     summary="Xóa sản phẩm",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa sản phẩm thành công"
     *     ),
     *     @OA\Response(response=404, description="Sản phẩm không tìm thấy")
     * )
     */
    public function destroy($id)
    {
        $product = $this->tikProductRepository->delete($id);
        if (!$product) {
            return Result::fail(self::errDelete);
        }
        return Result::success($product);
    }
}
