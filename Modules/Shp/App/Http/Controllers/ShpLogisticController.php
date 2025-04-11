<?php

namespace Modules\Shp\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Shp\Repositories\ShpLogistics\ShpLogisticInterface;

class ShpLogisticController extends Controller
{
    protected $shpLogisticRepository;

    const errMess = 'Dữ liệu không tồn tại';
    const errCreate = 'Thêm thất bại';
    const errUpdate = 'Cập nhật thất bại';
    const errSystem = 'Lỗi hệ thống';

    public function __construct(ShpLogisticInterface $shpLogisticRepository)
    {
        $this->shpLogisticRepository = $shpLogisticRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/shp-logistic",
     *     summary="Get list of Gtin records",
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
     *             @OA\Items(ref="#/components/schemas/ShpLogistic")
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
        $shpLogistics = $this->shpLogisticRepository->getAll($queryData);
        return Result::success($shpLogistics);
    }

    /**
     * @OA\Get(
     *     path="/api/shp-logistic/{id}",
     *     summary="Lấy thông tin vận chuyển",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Logistics record ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The logistics record",
     *         @OA\JsonContent(ref="#/components/schemas/ShpLogistic")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Logistics record not found"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        $shpLogistic = $this->shpLogisticRepository->findById($id);
        if (!$shpLogistic) {
            return Result::fail(self::errMess);
        }
        return Result::success($shpLogistic);
    }

    /**
     * @OA\Post(
     *     path="/api/shp-logistic",
     *     summary="Thêm thông tin vận chuyển",
     *     tags={"Shp"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"shp_id", "product_id", "shipping_fee", "enabled", "is_free", "size_id", "shipping_fee_type"},
     *             @OA\Property(property="shp_id", type="integer"),
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="shipping_fee", type="number", format="float"),
     *             @OA\Property(property="enabled", type="boolean"),
     *             @OA\Property(property="is_free", type="boolean"),
     *             @OA\Property(property="size_id", type="integer"),
     *             @OA\Property(property="shipping_fee_type", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="The logistics record was created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ShpLogistic")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'shp_id' => 'required|integer',
            'product_id' => 'required|integer',
            'shipping_fee' => 'required|numeric',
            'enabled' => 'required|boolean',
            'is_free' => 'required|boolean',
            'size_id' => 'required|integer',
            'shipping_fee_type' => 'required|string',
        ]);
        try {
            $shpLogistic = $this->shpLogisticRepository->create($request->all());
            if (!$shpLogistic) {
                return Result::fail(self::errCreate);
            }
            return Result::success($shpLogistic);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/shp-logistic/{id}",
     *     summary="Cập nhật thông tin vận chuyển",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Logistics record ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"shp_id", "product_id", "shipping_fee", "enabled", "is_free", "size_id", "shipping_fee_type"},
     *             @OA\Property(property="shp_id", type="integer"),
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="shipping_fee", type="number", format="float"),
     *             @OA\Property(property="enabled", type="boolean"),
     *             @OA\Property(property="is_free", type="boolean"),
     *             @OA\Property(property="size_id", type="integer"),
     *             @OA\Property(property="shipping_fee_type", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The logistics record was updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ShpLogistic")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Logistics record not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'shp_id' => 'required|integer',
            'product_id' => 'required|integer',
            'shipping_fee' => 'required|numeric',
            'enabled' => 'required|boolean',
            'is_free' => 'required|boolean',
            'size_id' => 'required|integer',
            'shipping_fee_type' => 'required|string',
        ]);
        try {
            $shpLogistic = $this->shpLogisticRepository->update($id, $request->all());
            if (!$shpLogistic) {
                return Result::fail(self::errUpdate);
            }
            return Result::success($shpLogistic);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/shp-logistic/{id}",
     *     summary="Xóa thông tin vận chuyển",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Logistics record ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The logistics record was deleted successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ShpLogistic")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Logistics record not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        $shpLogistic = $this->shpLogisticRepository->delete($id);
        if (!$shpLogistic) {
            return Result::fail(self::errMess);
        }
        return Result::success($shpLogistic);
    }
}
