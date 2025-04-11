<?php

namespace Modules\Shp\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Shp\Repositories\ShpPreorder\ShpPreorderInterface;

class ShpPreorderController extends Controller
{
    protected $shpPreorderRepository;
    const errMess = 'Không tìm thấy bản ghi';
    const errCreate = 'Tạo bản ghi thất bại';
    const errUpdate = 'Cập nhật bản ghi thất bại';
    const errDelete = 'Xóa bản ghi thất bại';
    public function __construct(ShpPreorderInterface $shpPreorderRepository)
    {
        $this->shpPreorderRepository = $shpPreorderRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/shp-preorder",
     *     summary="Danh sách sản phẩm đặt trước",
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
     *             @OA\Items(ref="#/components/schemas/ShpPreorder")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $shpPreorders = $this->shpPreorderRepository->getAll($queryData);
        return Result::success($shpPreorders);
    }

    /**
     * @OA\Get(
     *     path="/api/shp-preorder/{id}",
     *     tags={"Shp"},
     *     summary="Lấy chi tiết sản phẩm đặt trước",
     *     description="Lấy chi tiết sản phẩm đặt trước theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID sản phẩm đặt trước",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin sản phẩm đặt trước",
     *         @OA\JsonContent(ref="#/components/schemas/ShpPreorder")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy sản phẩm đặt trước"
     *     )
     * )
     */
    public function show($id)
    {
        $shpPreorder = $this->shpPreorderRepository->findById($id);
        if (!$shpPreorder) {
            return Result::fail(self::errMess);
        }
        return Result::success($shpPreorder);
    }

    /**
     * @OA\Post(
     *     path="/api/shp-preorder",
     *     tags={"Shp"},
     *     summary="Tạo mới sản phẩm đặt trước",
     *     description="Tạo mới một sản phẩm đặt trước với các thông tin yêu cầu.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ShpPreorder")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sản phẩm đặt trước đã được tạo thành công",
     *         @OA\JsonContent(ref="#/components/schemas/ShpPreorder")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'shp_id' => 'required|integer',
                'product_id' => 'required|integer',
                'is_pre_order' => 'required|boolean',
                'days_to_ship' => 'required|integer',
            ]);
            $data = $this->shpPreorderRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::errCreate);
            }
            return Result::success($data);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/shp-preorder/{id}",
     *     tags={"Shp"},
     *     summary="Cập nhật thông tin sản phẩm đặt trước",
     *     description="Cập nhật thông tin của sản phẩm đặt trước theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID sản phẩm đặt trước",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ShpPreorder")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật sản phẩm đặt trước thành công",
     *         @OA\JsonContent(ref="#/components/schemas/ShpPreorder")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy sản phẩm đặt trước"
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'shp_id' => 'required|integer',
                'product_id' => 'required|integer',
                'is_pre_order' => 'required|boolean',
                'days_to_ship' => 'required|integer',
            ]);
            $data = $this->shpPreorderRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errUpdate);
            }
            return Result::success($data);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/shp-preorders/{id}",
     *     tags={"Shp"},
     *     summary="Xóa sản phẩm đặt trước",
     *     description="Xóa sản phẩm đặt trước theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID sản phẩm đặt trước",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa sản phẩm đặt trước thành công"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy sản phẩm đặt trước"
     *     ),
     *   security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        $data = $this->shpPreorderRepository->delete($id);
        if (!$data) {
            return Result::fail(self::errDelete);
        }
        return Result::success($data);
    }
}
