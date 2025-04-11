<?php

namespace Modules\Shp\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Shp\Repositories\ShpSellerStocks\ShpSellerStockInterface;

class ShpSellerStockController extends Controller
{
    protected $shpSellerStockRepository;

    const errMess = 'Không tìm thấy dữ liệu';
    const errCreate = 'Tạo dữ liệu không thành công';
    const errSystem = 'Lỗi hệ thống';
    const errUpdate = 'Cập nhật dữ liệu không thành công';
    const errDelete = 'Xóa dữ liệu không thành công';

    public function __construct(ShpSellerStockInterface $shpSellerStockRepository)
    {
        $this->shpSellerStockRepository = $shpSellerStockRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/shp-seller-stocks",
     *     summary="Danh sách kho sản phẩm",
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
     *             @OA\Items(ref="#/components/schemas/ShpSellerStock")
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
        $shpSellerStocks = $this->shpSellerStockRepository->getAll($queryData);
        return Result::success($shpSellerStocks);
    }

    /**
     * @OA\Get(
     *     path="/api/shp-seller-stocks/{id}",
     *     summary="Lấy thông tin kho sản phẩm theo ID",
     *     tags={"Shp"},
     *     description="Trả về thông tin kho sản phẩm theo ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của kho sản phẩm",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin kho sản phẩm",
     *         @OA\JsonContent(ref="#/components/schemas/ShpSellerStock")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy dữ liệu"
     *     ),
     *   security={{"bearer": {}}}
     * )
     */
    public function show($id)
    {
        $shpSellerStock = $this->shpSellerStockRepository->findById($id);
        if (!$shpSellerStock) {
            return Result::fail(self::errMess);
        }
        return Result::success($shpSellerStock);
    }

    /**
     * @OA\Post(
     *     path="/api/shp-seller-stocks",
     *     summary="Tạo mới kho sản phẩm",
     *     tags={"Shp"},
     *     description="Tạo mới một bản ghi kho sản phẩm",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ShpSellerStock")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo mới kho sản phẩm thành công",
     *         @OA\JsonContent(ref="#/components/schemas/ShpSellerStock")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống"
     *     ),
     *   security={{"bearer": {}}}
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'shp_id' => 'nullable|integer',
            'product_id' => 'required|integer',
            'location_id' => 'nullable|string|max:255',
            'stock' => 'required|integer',
        ]);
        try {
            $shpSellerStock = $this->shpSellerStockRepository->create($request->all());
            if (!$shpSellerStock) {
                return Result::fail(self::errCreate);
            }
            return Result::success($shpSellerStock);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/shp-seller-stocks/{id}",
     *     summary="Cập nhật kho sản phẩm",
     *     tags={"Shp"},
     *     description="Cập nhật thông tin kho sản phẩm theo ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của kho sản phẩm",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ShpSellerStock")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(ref="#/components/schemas/ShpSellerStock")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy kho sản phẩm"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống"
     *     ),
     *   security={{"bearer": {}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'shp_id' => 'nullable|integer',
            'product_id' => 'required|integer',
            'location_id' => 'nullable|string|max:255',
            'stock' => 'required|integer',
        ]);
        try {
            $shpSellerStock = $this->shpSellerStockRepository->update($id, $request->all());
            if (!$shpSellerStock) {
                return Result::fail(self::errUpdate);
            }
            return Result::success($shpSellerStock);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/shp-seller-stocks/{id}",
     *     summary="Xóa kho sản phẩm",
     *     tags={"Shp"},
     *     description="Xóa kho sản phẩm theo ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của kho sản phẩm",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa kho sản phẩm thành công"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy kho sản phẩm"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống"
     *     ),
     *   security={{"bearer": {}}}
     * )
     */
    public function destroy($id)
    {
        $shpSellerStock = $this->shpSellerStockRepository->delete($id);
        if (!$shpSellerStock) {
            return Result::fail(self::errDelete);
        }
        return Result::success();
    }
}
