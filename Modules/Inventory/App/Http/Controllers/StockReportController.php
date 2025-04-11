<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Repositories\StockReport\StockReportInterface;

class StockReportController extends Controller
{
    protected $stockReportRepository;

    const errMess = 'Báo cáo tồn kho không tồn tại';
    const errCreate = 'Tạo mới báo cáo tồn kho thất bại';
    const errUpdate = 'Cập nhật thất bại';

    public function __construct(StockReportInterface $stockReportInterface)
    {
        $this->stockReportRepository = $stockReportInterface;
    }

    /**
     * @OA\Get(
     *     path="/api/stock-reports",
     *     summary="Lấy danh sách báo cáo tồn kho",
     *     description="Trả về danh sách báo cáo tồn kho với các bộ lọc tùy chọn",
     *     operationId="getStockReports",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Số trang cần lấy",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Số lượng báo cáo tồn kho mỗi trang",
     *         @OA\Schema(
     *             type="integer",
     *             example=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="material_id",
     *         in="query",
     *         required=false,
     *         description="ID nguyên vật liệu (lọc theo nguyên vật liệu)",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         required=false,
     *         description="ID sản phẩm (lọc theo sản phẩm)",
     *         @OA\Schema(
     *             type="integer",
     *             example=2
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="warehouse_id",
     *         in="query",
     *         required=false,
     *         description="ID kho (lọc theo kho)",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách báo cáo tồn kho",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/StockReportModel")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="limit", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=100)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi khi truy vấn",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="fail"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Lỗi khi truy vấn"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="fail"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Lỗi hệ thống"
     *             )
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $data = $this->stockReportRepository->getAll($queryData);
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/stock-reports/{id}",
     *     summary="Lấy thông tin báo cáo tồn kho",
     *     description="Trả về thông tin chi tiết của báo cáo tồn kho theo ID",
     *     operationId="getStockReportById",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của báo cáo tồn kho",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin báo cáo tồn kho thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/StockReportModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy báo cáo tồn kho",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy báo cáo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Lỗi hệ thống")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        $stock = $this->stockReportRepository->findById($id);
        if (!$stock) {
            return Result::fail(self::errMess);
        }
        return Result::success($stock);
    }

    /**
     * @OA\Post(
     *     path="/api/stock-reports",
     *     operationId="createStockReport",
     *     tags={"Inventory"},
     *     summary="Tạo báo cáo tồn kho",
     *     description="Tạo mới báo cáo tồn kho",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/StockReportModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Báo cáo tồn kho đã được tạo thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/StockReportModel")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Yêu cầu không hợp lệ"),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'material_id' => 'nullable|exists:inv_materials,id',
            'product_id' => 'nullable|exists:inv_products,id',
            'warehouse_id' => 'required|exists:inv_warehouses,id',
            'total_in' => 'required|numeric',
            'total_out' => 'required|numeric',
            'stock_balance' => 'required|numeric',
            'actual_stock' => 'required|numeric',
            'stock_difference' => 'required|numeric',
        ]);
        try {
            $stock = $this->stockReportRepository->create($data);
            if (!$stock) {
                return Result::fail(self::errCreate);
            }
            return Result::success($stock);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errCreate);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/stock-reports/{id}",
     *     summary="Cập nhật báo cáo tồn kho",
     *     description="Cập nhật thông tin báo cáo tồn kho theo ID",
     *     operationId="updateStockReport",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của báo cáo tồn kho cần cập nhật",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu cần cập nhật cho báo cáo tồn kho",
     *         @OA\JsonContent(ref="#/components/schemas/StockReportModel")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật báo cáo tồn kho thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/StockReportModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy báo cáo tồn kho",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy báo cáo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Lỗi hệ thống")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'material_id' => 'nullable|exists:inv_materials,id',
            'product_id' => 'nullable|exists:inv_products,id',
            'warehouse_id' => 'required|exists:inv_warehouses,id',
            'total_in' => 'required|numeric',
            'total_out' => 'required|numeric',
            'stock_balance' => 'required|numeric',
            'actual_stock' => 'required|numeric',
            'stock_difference' => 'required|numeric',
        ]);

        $stockReport = $this->stockReportRepository->update($id, $data);

        if (!$stockReport) {
            return Result::fail(self::errUpdate);
        }

        return Result::success($stockReport);
    }

    /**
     * @OA\Delete(
     *     path="/api/stock-reports/{id}",
     *     summary="Xóa báo cáo tồn kho",
     *     description="Xóa báo cáo tồn kho theo ID",
     *     operationId="deleteStockReport",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của báo cáo tồn kho",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa báo cáo thành công",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Báo cáo tồn kho đã được xóa thành công"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Báo cáo không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="fail"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Không tìm thấy báo cáo tồn kho"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="fail"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Lỗi hệ thống, vui lòng thử lại sau"
     *             )
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $stockReport = $this->stockReportRepository->findById($id);
            if (!$stockReport) {
                return Result::fail(self::errMess);
            }

            $stockReport->delete();

            return Result::success($stockReport);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return Result::fail(self::errMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/stock-reports/inventory-totals",
     *     operationId="getInventoryTotals",
     *     tags={"Inventory"},
     *     summary="Lấy tổng nhập xuất cho báo cáo tồn kho",
     *     description="Trả về tổng số lượng nhập và xuất dựa trên lịch sử giao dịch",
     *     @OA\Parameter(
     *         name="warehouse_id",
     *         in="query",
     *         description="ID của kho",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="material_id",
     *         in="query",
     *         description="ID của nguyên vật liệu",
     *         required=false,
     *         @OA\Schema(type="integer", nullable=true)
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         description="ID của sản phẩm",
     *         required=false,
     *         @OA\Schema(type="integer", nullable=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dữ liệu tổng nhập xuất",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_in", type="number", example=500),
     *                 @OA\Property(property="total_out", type="number", example=150)
     *             )
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getInventoryTotals(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:inv_warehouses,id',
            'material_id' => 'nullable|exists:inv_materials,id',
            'product_id' => 'nullable|exists:inv_products,id',
        ]);
        Log::debug('Hello');
        try {
            $totals = $this->stockReportRepository->getInventoryTotals(
                $request->all()
            );

            return Result::success($totals);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errMess);
        }
    }
}
