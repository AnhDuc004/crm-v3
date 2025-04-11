<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Repositories\SalesOrder\SalesOrderInterface;

class SalesOrderController extends Controller
{
    protected $salesOrderRepository;

    const errMess = 'Không tìm thấy xuất kho';
    const errSystem = 'Lỗi hệ thống';
    const errCreate = 'Lỗi khi tạo xuất kho';
    const errUpdate = 'Lỗi khi cập nhật xuất kho';

    public function __construct(SalesOrderInterface $salesOrderRepository)
    {
        $this->salesOrderRepository =  $salesOrderRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/sales-orders",
     *     summary="Danh sách xuất kho",
     *     description="Truy xuất danh sách xuất kho với các bộ lọc tùy chọn và phân trang",
     *     operationId="getSalesOrders",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="query",
     *         description="Lọc theo ID khách hàng",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Số lượng kết quả trên mỗi trang",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Số trang để phân trang",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of Sales Orders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SalesOrderModel")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
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
        $data = $this->salesOrderRepository->getAll($queryData);
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/sales-orders/{id}",
     *     summary="Lấy xuất kho theo ID",
     *     description="Lấy xuất kho theo ID",
     *     operationId="getSalesOrderById",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id của xuất kho",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy thông tin xuất kho thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/SalesOrderModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Không tìm thấy")
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
            $salesOrder = $this->salesOrderRepository->findById($id);
            if (!$salesOrder) {
                return Result::fail(self::errMess);
            }
            return Result::success($salesOrder);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/sales-orders",
     *     summary="Tạo mới xuất kho cùng với danh sách sản phẩm",
     *     description="Tạo mới đơn xuất kho và thêm các sản phẩm liên quan",
     *     operationId="createSalesOrder",
     *     tags={"Inventory"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu xuất kho và danh sách sản phẩm",
     *         @OA\JsonContent(
     *             @OA\Property(property="customer_id", type="integer", example=1),
     *             @OA\Property(property="order_number", type="string", example="SO-001"),
     *             @OA\Property(property="order_date", type="string", format="date", example="2024-02-26"),
     *             @OA\Property(property="warehouse_id", type="string", example="1"),
     *             @OA\Property(property="status", type="string", example="1.Chờ xác nhận 2.Đã xuất kho 3.Hoàn Thành"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="number", format="double", example=2),
     *                     @OA\Property(property="price", type="number", format="double", example=500000.25)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Xuất kho thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/SalesOrderModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid input data")
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
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:inv_warehouses,id',
            'order_number' => 'required|string|unique:inv_sales_orders,order_number',
            'order_date' => 'required|date',
            'status' => 'required|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:inv_products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);
        Log::debug($request->all());
        try {
            DB::beginTransaction();

            $salesOrder = $this->salesOrderRepository->create($request->all());

            DB::commit();

            return Result::success($salesOrder);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating sales order: ' . $e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/sales-orders/{id}",
     *     summary="Cập nhật đơn xuất kho và danh sách sản phẩm",
     *     description="Cập nhật thông tin đơn xuất kho và các sản phẩm liên quan",
     *     operationId="updateSalesOrder",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của đơn xuất kho cần cập nhật",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu cập nhật xuất kho và danh sách sản phẩm",
     *         @OA\JsonContent(
     *             @OA\Property(property="customer_id", type="integer", example=1),
     *             @OA\Property(property="order_date", type="string", format="date", example="2024-02-26"),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="pending",
     *                 description="Trạng thái đơn hàng (pending, processing, completed, cancelled)"
     *             ),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example=1,
     *                         description="ID của item (nếu là item đã tồn tại)"
     *                     ),
     *                     @OA\Property(
     *                         property="product_id",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="quantity",
     *                         type="number",
     *                         format="double",
     *                         example=2
     *                     ),
     *                     @OA\Property(
     *                         property="price",
     *                         type="number",
     *                         format="double",
     *                         example=500000.25
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật xuất kho thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/SalesOrderModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invalid input data"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy đơn xuất kho",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Sales order not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Không thể cập nhật do trạng thái đơn hàng",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Cannot update completed order"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="System error"
     *             )
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:inv_warehouses,id',
            'order_date' => 'required|date',
            'status' => 'required|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:inv_products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);
        Log::debug($request->all());

        try {
            $salesOrder = $this->salesOrderRepository->findById($id);

            if (!$salesOrder) {
                return Result::fail(self::errMess);
            }
            $salesOrder = $this->salesOrderRepository->update($id, $request->all());
            if (!$salesOrder) {
                return Result::fail(self::errUpdate);
            }

            return Result::success($salesOrder);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errUpdate);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/sales-orders/{id}",
     *     summary="Xóa xuất Kho",
     *     description="Xóa xuất Kho theo ID",
     *     operationId="deleteSalesOrder",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id xuất Kho",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xoá xuất Kho thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sales Order deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy!",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sales Order not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống!",
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
            $salesOrder = $this->salesOrderRepository->findById($id);

            if (!$salesOrder) {
                return Result::fail(self::errMess);
            }
            $data = $this->salesOrderRepository->delete($id);

            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

}
