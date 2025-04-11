<?php

namespace Modules\Tik\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Tik\Repositories\TikProductImage\TikProductImageInterface;

class TikProductImageController extends Controller
{
    protected $tikProductImageRepository;

    const errMess = "Không tìm thấy ảnh";
    const errCreate = "Thêm ảnh thất bại";
    const errValidate = "Dữ liệu không hợp lệ";
    const errSystem = "Lỗi hệ thống";
    const errUpdate = "Cập nhật ảnh thất bại";
    public function __construct(TikProductImageInterface $tikProductImageRepository)
    {
        $this->tikProductImageRepository = $tikProductImageRepository;
    }

    public function index(Request $request)
    {
        $queryData = $request->all();
        $data = $this->tikProductImageRepository->getAll($queryData);
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/tik-product-images/{id}",
     *     summary="Lấy chi tiết ảnh sản phẩm",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của ảnh sản phẩm",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chi tiết ảnh sản phẩm",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/TikProductImage")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy ảnh")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $tikProductImage = $this->tikProductImageRepository->findById($id);
        if (!$tikProductImage) {
            return Result::fail(self::errMess);
        }
        return Result::success($tikProductImage);
    }

    /**
     * @OA\Post(
     *     path="/api/tik-product-image",
     *     summary="Tạo mới ảnh sản phẩm",
     *     tags={"Tik"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="url_list", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="thumb_url_list", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="height", type="integer"),
     *             @OA\Property(property="width", type="integer"),
     *             @OA\Property(property="product_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/TikProductImage")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi dữ liệu",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Thêm ảnh thất bại")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|integer|exists:tik_products,id',
        ]);
        try {
            $tikProductImage = $this->tikProductImageRepository->create($request->all());
            if (!$tikProductImage) {
                return Result::fail(self::errCreate);
            }
            return Result::success($tikProductImage);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tik-product-images/{id}",
     *     summary="Cập nhật ảnh sản phẩm",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của ảnh sản phẩm",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="url_list", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="thumb_url_list", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="height", type="integer"),
     *             @OA\Property(property="width", type="integer"),
     *             @OA\Property(property="product_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/TikProductImage")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy ảnh")
     *         )
     *     )
     * )
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|integer|exists:tik_products,id',
        ]);
        try {
            $tikProductImage = $this->tikProductImageRepository->findById($id);
            if (!$tikProductImage) {
                return Result::fail(self::errMess);
            }
            $data = $this->tikProductImageRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errUpdate);
            }
            return Result::success($tikProductImage);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    public function destroy($id)
    {
        try {
            $tikProductImage = $this->tikProductImageRepository->findById($id);
            if (!$tikProductImage) {
                return Result::fail(self::errMess);
            }
            $tikProductImage = $this->tikProductImageRepository->delete($id);
            return Result::success($tikProductImage);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    public function updateWithImages($id, Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|integer|exists:tik_products,id',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            $data = $this->tikProductImageRepository->updateWithImages($id, $request->all());
            if (!$data) {
                return Result::fail(self::errMess);
            }
            return Result::success($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }
}
