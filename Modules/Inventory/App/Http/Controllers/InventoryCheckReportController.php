<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Repositories\InventoryCheckReport\InventoryCheckReportInterface;

class InventoryCheckReportController extends Controller
{
    protected $inventoryCheckReportRepository;

    const errMess = 'Báo cáo nhập nguyên vật liệu không tồn tại';
    const errSystem = 'Lỗi hệ thống';
    const errCreate = 'Thêm mới nhập nguyên vật liệu kiểm thất bại';
    const errUpdate = 'Cập nhật thất bại';

    public function __construct(InventoryCheckReportInterface $inventoryCheckReportInterface)
    {
        $this->inventoryCheckReportRepository = $inventoryCheckReportInterface;
    }

    /**
     * @OA\Get(
     *     path="/api/inventory-check-reports",
     *     summary="Lấy tất cả các báo cáo nhập nguyên vật liệu",
     *     description="Lấy tất cả các báo cáo nhập nguyên vật liệu",
     *     operationId="getInventoryCheckReports",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="material_id",
     *         in="query",
     *         required=false,
     *         description="Lọc theo ID của nguyên vật liệu",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         required=false,
     *         description="Lọc theo ID của sản phẩm",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="warehouse_id",
     *         in="query",
     *         required=false,
     *         description="Lọc theo ID của thông tin Kho",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="check_date",
     *         in="query",
     *         required=false,
     *         description="Lọc theo ngày",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách tất cả các báo cáo nhập nguyên vật liệu",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/InventoryCheckReportModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="System error")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $data = $this->inventoryCheckReportRepository->getAll($queryData);
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/inventory-check-reports/{id}",
     *     summary="Lấy thông tin Báo cáo nhập nguyên vật liệu",
     *     description="Lấy Báo cáo nhập nguyên vật liệu theo ID của nó",
     *     operationId="getInventoryCheckReport",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của báo cáo nhập nguyên vật liệu",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Báo cáo nhập nguyên vật liệu đã được truy xuất thành công",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryCheckReportModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Report not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="System error")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        try {
            $iventory = $this->inventoryCheckReportRepository->findById($id);

            if (!$iventory) {
                return Result::fail(self::errMess);
            }

            return Result::success($iventory);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/inventory-check-reports",
     *     summary="Thêm mới Báo cáo nhập nguyên vật liệu mới",
     *     description="Thêm mới Báo cáo nhập nguyên vật liệu mới",
     *     operationId="storeInventoryCheckReport",
     *     tags={"Inventory"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/InventoryCheckReportModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo mới thành công",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryCheckReportModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid input")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'material_id' => 'nullable|integer|exists:inv_materials,id',
            'product_id' => 'nullable|integer|exists:inv_products,id',
            'warehouse_id' => 'required|integer|exists:inv_warehouses,id',
            'check_date' => 'required|date',
            'actual_stock' => 'required|numeric',
            'stock_difference' => 'required|numeric',
            'created_by' => 'nullable|integer|exists:users,id',
            'updated_by' => 'nullable|integer|exists:users,id',
        ]);
        try {
            $iventory = $this->inventoryCheckReportRepository->create($data);
            if (!$iventory) {
                return Result::fail(self::errCreate);
            }
            return Result::success($iventory);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/inventory-check-reports/{id}",
     *     summary="Cập nhật Báo cáo nhập nguyên vật liệu",
     *     description="Cập nhật Báo cáo nhập nguyên vật liệu theo ID của nó",
     *     operationId="updateInventoryCheckReport",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của Báo cáo nhập nguyên vật liệu",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/InventoryCheckReportModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryCheckReportModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Đầu vào không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đầu vào không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="không tìm thấy",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Report not found")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'material_id' => 'nullable|integer|exists:inv_materials,id',
            'product_id' => 'nullable|integer|exists:inv_products,id',
            'warehouse_id' => 'required|integer|exists:inv_warehouses,id',
            'check_date' => 'required|date',
            'actual_stock' => 'required|numeric',
            'stock_difference' => 'required|numeric',
            'created_by' => 'nullable|integer|exists:users,id',
            'updated_by' => 'nullable|integer|exists:users,id',
        ]);

        try {
            $iventory = $this->inventoryCheckReportRepository->update($id, $data);

            if (!$iventory) {
                return Result::fail(self::errUpdate);
            }

            return Result::success($iventory);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/inventory-check-reports/{id}",
     *     summary="Xóa Báo cáo nhập nguyên vật liệu",
     *     description="Xóa Báo cáo nhập nguyên vật liệu theo ID của nó",
     *     operationId="deleteInventoryCheckReport",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của báo cáo kiểm kho",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Inventory Check Report deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="không tìm thấy",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Report not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="System error")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $iventory = $this->inventoryCheckReportRepository->findById($id);

            if (!$iventory) {
                return Result::fail(self::errMess);
            }
            $data = $this->inventoryCheckReportRepository->delete($id);
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }
}
