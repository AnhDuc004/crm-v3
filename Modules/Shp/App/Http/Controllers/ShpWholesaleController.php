<?php

namespace Modules\Shp\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Shp\Repositories\ShpWholesale\ShpWholesaleInterface;

class ShpWholesaleController extends Controller
{
    protected $shpWholesaleRepository;

    const errMess = 'Không tìm thấy wholesale';
    const errCreate = 'Tạo wholesale thất bại';
    const errUpdate = 'Cập nhật wholesale thất bại';
    const errDelete = 'Xóa wholesale thất bại';
    public function __construct(ShpWholesaleInterface $shpWholesaleRepository)
    {
        $this->shpWholesaleRepository = $shpWholesaleRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/shp-wholesale",
     *     summary="Danh sách wholesale",
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
     *             @OA\Items(ref="#/components/schemas/ShpWholesale")
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
        $wholesales = $this->shpWholesaleRepository->getAll($queryData);
        return Result::success($wholesales);
    }

    /**
     * @OA\Get(
     *     path="/api/shp-wholesale/{id}",
     *     summary="Id của wholesale",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của wholesale",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin wholesale",
     *         @OA\JsonContent(ref="#/components/schemas/ShpWholesale")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy wholesale"
     *     ),
     *   security={{"bearer":{}}}
     * )
     */

    public function show($id)
    {
        $wholesale = $this->shpWholesaleRepository->findById($id);
        if (!$wholesale) {
            return Result::fail(self::errMess);
        }
        return Result::success($wholesale);
    }

    /**
     * @OA\Post(
     *     path="/api/shp-wholesale",
     *     summary="Tạo mới wholesale",
     *     tags={"Shp"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "min_count", "max_count", "unit_price"},
     *             @OA\Property(property="shp_id", type="integer", description="ID của wholesale trong hệ thống SHP"),
     *             @OA\Property(property="product_id", type="integer", description="ID của sản phẩm"),
     *             @OA\Property(property="min_count", type="integer", description="Số lượng tối thiểu trong wholesale"),
     *             @OA\Property(property="max_count", type="integer", description="Số lượng tối đa trong wholesale"),
     *             @OA\Property(property="unit_price", type="number", format="float", description="Giá mỗi đơn vị trong wholesale")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wholesale được tạo thành công",
     *         @OA\JsonContent(ref="#/components/schemas/ShpWholesale")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi tạo wholesale"
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'shp_id' => 'nullable|integer',
            'product_id' => 'required|integer',
            'min_count' => 'required|integer',
            'max_count' => 'required|integer',
            'unit_price' => 'required|numeric',
        ]);
        try {
            $wholesale = $this->shpWholesaleRepository->create($request->all());
            if (!$wholesale) {
                return Result::fail(self::errCreate);
            }
            return Result::success($wholesale);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/shp-wholesale/{id}",
     *     summary="Cập nhật wholesale",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của wholesale",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "min_count", "max_count", "unit_price"},
     *             @OA\Property(property="shp_id", type="integer", description="ID của wholesale trong hệ thống SHP"),
     *             @OA\Property(property="product_id", type="integer", description="ID của sản phẩm"),
     *             @OA\Property(property="min_count", type="integer", description="Số lượng tối thiểu trong wholesale"),
     *             @OA\Property(property="max_count", type="integer", description="Số lượng tối đa trong wholesale"),
     *             @OA\Property(property="unit_price", type="number", format="float", description="Giá mỗi đơn vị trong wholesale")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wholesale được cập nhật thành công",
     *         @OA\JsonContent(ref="#/components/schemas/ShpWholesale")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy wholesale"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi cập nhật wholesale"
     *     ),
     *   security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'shp_id' => 'nullable|integer',
            'product_id' => 'required|integer',
            'min_count' => 'required|integer',
            'max_count' => 'required|integer',
            'unit_price' => 'required|numeric',
        ]);
        try {
            $wholesale = $this->shpWholesaleRepository->update($id, $request->all());
            if (!$wholesale) {
                return Result::fail(self::errUpdate);
            }
            return Result::success($wholesale);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/shp-wholesale/{id}",
     *     summary="Xóa wholesale",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của wholesale",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wholesale đã được xóa"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy wholesale"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi xóa wholesale"
     *     ),
     *   security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        $wholesale = $this->shpWholesaleRepository->delete($id);
        if (!$wholesale) {
            return Result::fail(self::errDelete);
        }
        return Result::success($wholesale);
    }
}
