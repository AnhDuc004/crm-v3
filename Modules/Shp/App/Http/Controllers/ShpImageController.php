<?php

namespace Modules\Shp\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Shp\Repositories\ShpImage\ShpImageInterface;

class ShpImageController extends Controller
{
    protected $shpImageRepository;

    const errMess = 'Hình ảnh không tồn tại';
    public function __construct(ShpImageInterface $shpImageRepository)
    {
        $this->shpImageRepository = $shpImageRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/shp-image",
     *     summary="Get list of Imgae",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="shp_id",
     *         in="query",
     *         required=false,
     *         description="Filter by SHP ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         required=false,
     *         description="Filter by Product ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ShpImage")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index()
    {
        $queryData = request()->all();
        $shpImages = $this->shpImageRepository->getAll($queryData);
        return Result::success($shpImages);
    }

    /**
     * @OA\Get(
     *     path="/api/shp-image/{id}",
     *     summary="Lấy chi tiết hình ảnh sản phẩm",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của hình ảnh sản phẩm",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chi tiết hình ảnh sản phẩm",
     *         @OA\JsonContent(ref="#/components/schemas/ShpImage")
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy hình ảnh"),
     *     @OA\Response(response=500, description="Lỗi máy chủ nội bộ")
     * ),
     *    security={{"bearer":{}}}
     */
    public function show($id)
    {
        $shpImage = $this->shpImageRepository->findById($id);
        if (!$shpImage) {
            return Result::fail(self::errMess);
        }
        return Result::success($shpImage);
    }

    /**
     * @OA\Post(
     *     path="/api/shp-image",
     *     summary="Tạo mới hình ảnh sản phẩm",
     *     tags={"Shp"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"shp_id", "product_id", "image_url"},
     *                 @OA\Property(property="shp_id", type="integer", description="ID hình ảnh từ hệ thống SHP"),
     *                 @OA\Property(property="product_id", type="integer", description="ID sản phẩm"),
     *                 @OA\Property(property="image_url", type="string", maxLength=255, description="URL của hình ảnh"),
     *                 @OA\Property(property="image_ratio", type="string", enum={"1:1", "3:4"}, nullable=true, description="Tỷ lệ hình ảnh")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Hình ảnh sản phẩm đã được tạo",
     *         @OA\JsonContent(ref="#/components/schemas/ShpImage")
     *     ),
     *     @OA\Response(response=400, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=500, description="Lỗi máy chủ nội bộ")
     * ),
     *    security={{"bearer":{}}}
     */
    public function store(Request $request)
    {
        $request->validate([
            'shp_id' => 'nullable|integer',
            'product_id' => 'nullable|integer|exists:shp_products,id',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_ratio' => 'nullable|max:255',
        ]);
        try {
            $data = $this->shpImageRepository->create($request->all());
            return Result::success($data);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/shp-image/{id}",
     *     summary="Cập nhật hình ảnh sản phẩm",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID hình ảnh sản phẩm",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"shp_id", "product_id", "image_url"},
     *                 @OA\Property(property="shp_id", type="integer", description="ID hình ảnh từ hệ thống SHP"),
     *                 @OA\Property(property="product_id", type="integer", description="ID sản phẩm"),
     *                 @OA\Property(property="image_url", type="string", maxLength=255, description="URL của hình ảnh"),
     *                 @OA\Property(property="image_ratio", type="string", enum={"1:1", "3:4"}, nullable=true, description="Tỷ lệ hình ảnh")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hình ảnh sản phẩm đã được cập nhật",
     *         @OA\JsonContent(ref="#/components/schemas/ShpImage")
     *     ),
     *     @OA\Response(response=404, description="Hình ảnh không tồn tại"),
     *     @OA\Response(response=400, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=500, description="Lỗi máy chủ nội bộ")
     * ),
     *     security={{"bearer":{}}}
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'shp_id' => 'required|integer',
            'product_id' => 'required|integer|exists:shp_products,id',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_ratio' => 'nullable|max:255',
        ]);
        try {
            $data['image_file'] = $request->file('image_file');
            $data = $this->shpImageRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errMess);
            }
            return Result::success($data);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/shp-image/{id}",
     *     summary="Xóa hình ảnh sản phẩm",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID hình ảnh sản phẩm cần xóa",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hình ảnh sản phẩm đã được xóa thành công"
     *     ),
     *     @OA\Response(response=404, description="Hình ảnh không tồn tại"),
     *     @OA\Response(response=500, description="Lỗi máy chủ nội bộ")
     * ),
     *   security={{"bearer":{}}}
     */
    public function destroy($id)
    {
        $data = $this->shpImageRepository->delete($id);
        if (!$data) {
            return Result::fail(self::errMess);
        }
        return Result::success($data);
    }
}
