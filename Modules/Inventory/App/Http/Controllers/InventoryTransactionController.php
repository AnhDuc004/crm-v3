<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Repositories\InventoryTransaction\InventoryTransactionInterface;

class InventoryTransactionController extends Controller
{
    protected $inventoryTransactionRepository;

    const errMess = 'Nhập nguyên vật liệu không tồn tại';
    const errCreate = 'Thêm mới nhập nguyên vật liệu thất bại';
    const errUpdate = 'Cập nhật nhập nguyên vật liệu thất bại';
    const errSystem = 'Lỗi hệ thống';

    public function __construct(InventoryTransactionInterface $inventoryTransactionInterface)
    {
        $this->inventoryTransactionRepository = $inventoryTransactionInterface;
    }

    /**
     * @OA\Get(
     *     path="/api/inventory-transactions",
     *     operationId="getInventoryTransactions",
     *     tags={"Inventory"},
     *     summary="Danh sách nhập nguyên vật liệu",
     *     description="Lấy danh sách nhập nguyên vật liệu với các tiêu chí lọc và phân trang.",
     *     @OA\Parameter(
     *         name="transaction_type",
     *         in="query",
     *         required=false,
     *         description="Loại giao dịch (nhập/xuất) để lọc",
     *         @OA\Schema(
     *             type="string",
     *             example="nhập"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="material_id",
     *         in="query",
     *         required=false,
     *         description="ID của nguyên vật liệu để lọc",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         required=false,
     *         description="ID của sản phẩm để lọc",
     *         @OA\Schema(
     *             type="integer",
     *             example=2
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="warehouse_id",
     *         in="query",
     *         required=false,
     *         description="ID của kho để lọc",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Số lượng bản ghi mỗi trang",
     *         @OA\Schema(
     *             type="integer",
     *             example=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Số trang",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách giao dịch kho",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/InventoryTransactionModel")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy giao dịch kho",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy dữ liệu")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $inventory = $this->inventoryTransactionRepository->getAll($queryData);
        return Result::success($inventory);
    }

    /**
     * @OA\Get(
     *     path="/api/inventory-transactions/{id}",
     *     operationId="showInventoryTransaction",
     *     tags={"Inventory"},
     *     summary="Lấy thông tin nhập nguyên vật liệu ID",
     *     description="Trả về thông tin nhập nguyên vật liệu theo ID được cung cấp.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID nhập nguyên vật liệu",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Nhập nguyên vật liệu được truy xuất",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/InventoryTransactionModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy nhập nguyên vật liệu với ID này",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy giao dịch kho với ID này")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Lỗi hệ thống, vui lòng thử lại")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        try {
            $iventory = $this->inventoryTransactionRepository->findById($id);

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
     *     path="/api/inventory-transactions",
     *     operationId="storeInventoryTransaction",
     *     tags={"Inventory"},
     *     summary="Tạo nhập nguyên vật liệu mới",
     *     description="Thực hiện thêm mới nhập nguyên vật liệu",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/InventoryTransactionModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Nhập nguyên vật liệu được tạo thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/InventoryTransactionModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Lỗi hệ thống, vui lòng thử lại")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'transaction_type' => 'required|string|max:255',
            'material_id' => 'nullable|exists:inv_materials,id',
            'product_id' => 'nullable|exists:inv_products,id',
            'quantity' => 'required|numeric',
            'warehouse_id' => 'required|exists:inv_warehouses,id',
        ]);
        try {
            $iventory = $this->inventoryTransactionRepository->create($data);
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
     *     path="/api/inventory-transactions/{id}",
     *     operationId="updateInventoryTransaction",
     *     tags={"Inventory"},
     *     summary="Cập nhật nhập nguyên vật liệu",
     *     description="Cập nhật thông tin của  nhập nguyên vật liệu.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID nhập nguyên vật liệu",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu nhập nguyên vật liệu cần cập nhật",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"transaction_type", "quantity", "warehouse_id"},
     *             @OA\Property(property="transaction_type", type="string", description="Loại giao dịch", example="nhập"),
     *             @OA\Property(property="material_id", type="integer", description="Nguyên vật liệu liên quan", example=1),
     *             @OA\Property(property="product_id", type="integer", description="Sản phẩm liên quan", example=2),
     *             @OA\Property(property="quantity", type="number", format="float", description="Số lượng giao dịch", example=100.50),
     *             @OA\Property(property="warehouse_id", type="integer", description="ID kho", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Nhập nguyên vật liệu được cập nhật thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/InventoryTransactionModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy giao dịch kho với ID này")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'transaction_type' => 'required|string|max:255',
            'material_id' => 'nullable|exists:inv_materials,id',
            'product_id' => 'nullable|exists:inv_products,id',
            'quantity' => 'required|numeric',
            'warehouse_id' => 'required|exists:inv_warehouses,id'
        ]);

        try {
            $iventory = $this->inventoryTransactionRepository->update($id, $data);

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
     *     path="/api/inventory-transactions/{id}",
     *     operationId="deleteInventoryTransaction",
     *     tags={"Inventory"},
     *     summary="Xóa nhập nguyên vật liệu",
     *     description="Xóa nhập nguyên vật liệu theo ID của nó",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của nhập nguyên vật liệu cần xóa",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa nhập nguyên vật liệu thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Giao dịch kho đã được xóa thành công")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Giao dịch kho không tồn tại")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $iventory = $this->inventoryTransactionRepository->findById($id);

            if (!$iventory) {
                return Result::fail(self::errMess);
            }
            $data = $this->inventoryTransactionRepository->delete($id);
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }
}
