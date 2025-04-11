<?php

namespace Modules\Shp\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Shp\Repositories\ShpTaxInfo\ShpTaxInfoInterface;

class ShpTaxInfoController extends Controller
{
    protected $shpTaxInfoRepository;
    const errMess = 'Không tìm thấy dữ liệu';
    const errCreate = 'Thêm thất bại';
    const errSystem = 'Lỗi hệ thống';
    const errUpdate = 'Cập nhật thất bại';
    const errDelete = 'Xóa thất bại';

    public function __construct(ShpTaxInfoInterface $shpTaxInfoRepository)
    {
        $this->shpTaxInfoRepository = $shpTaxInfoRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/shp-tax-info",
     *     summary="Danh sách thông tin thuế sản phẩm",
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
     *             @OA\Items(ref="#/components/schemas/ShpTaxInfo")
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
        $shpTaxInfo = $this->shpTaxInfoRepository->getAll($queryData);
        return Result::success($shpTaxInfo);
    }

    /**
     * @OA\Get(
     *     path="/api/shp-tax-info/{id}",
     *     summary="Lấy thông tin thuế của sản phẩm theo ID",
     *     description="Trả về thông tin thuế của sản phẩm dựa trên ID",
     *     operationId="getShpTaxInfoById",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của thông tin thuế sản phẩm",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin thuế của sản phẩm",
     *         @OA\JsonContent(ref="#/components/schemas/ShpTaxInfo")
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy dữ liệu"),
     *     @OA\Response(response=500, description="Lỗi hệ thống")
     * ),
     *   security={{"bearer": {}}}
     */
    public function show($id)
    {
        $shpTaxInfo = $this->shpTaxInfoRepository->findById($id);
        if (!$shpTaxInfo) {
            return Result::fail(self::errMess);
        }
        return Result::success($shpTaxInfo);
    }

    /**
     * @OA\Post(
     *     path="/api/shp-tax-info",
     *     summary="Tạo mới thông tin thuế sản phẩm",
     *     description="Tạo mới bản ghi thông tin thuế cho sản phẩm",
     *     tags={"Shp"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ShpTaxInfo"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thêm mới thành công",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ShpTaxInfo"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi khi thêm mới"
     *     ),
     *   security={{"bearer": {}}}
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'shp_id' => 'nullable|integer',
            'product_id' => 'required|integer',
            'ncm' => 'nullable|string|max:8',
            'tax_type' => 'nullable|string',
            'tax_rate' => 'nullable|numeric',
        ]);
        try {
            $shpSellerStock = $this->shpTaxInfoRepository->create($request->all());
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
     *     path="/api/shp-tax-info/{id}",
     *     summary="Cập nhật thông tin thuế sản phẩm",
     *     description="Cập nhật bản ghi thông tin thuế cho sản phẩm",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của bản ghi thông tin thuế sản phẩm",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ShpTaxInfo"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ShpTaxInfo"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi khi cập nhật"
     *     ),
     *   security={{"bearer": {}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'shp_id' => 'nullable|integer',
            'product_id' => 'required|integer',
            'ncm' => 'nullable|string|max:8',
            'tax_type' => 'nullable|string',
            'tax_rate' => 'nullable|numeric',
        ]);
        try {
            $shpSellerStock = $this->shpTaxInfoRepository->update($id, $request->all());
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
     *     path="/api/shp-tax-info/{id}",
     *     summary="Xóa thông tin thuế sản phẩm",
     *     description="Xóa bản ghi thông tin thuế cho sản phẩm",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của bản ghi thông tin thuế sản phẩm",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thành công"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi khi xóa"
     *     ),
     *   security={{"bearer": {}}}
     * )
     */
    public function destroy($id)
    {
        $shpTaxInfo = $this->shpTaxInfoRepository->delete($id);
        if (!$shpTaxInfo) {
            return Result::fail(self::errDelete);
        }
        return Result::success($shpTaxInfo);
    }
}
