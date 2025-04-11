<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Repositories\Warehouse\WarehouseInterface;

class WarehouseController extends Controller
{
    protected $warehouseRepository;

    const errCreate = 'Tạo mói thất bại';
    const errUpdate = 'Cập nhật thất bại';
    const errMess = 'Kho không tồn tại';

    public function __construct(WarehouseInterface $warehouseRepository)
    {
        $this->warehouseRepository = $warehouseRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/warehouses",
     *     operationId="getWarehouses",
     *     tags={"Inventory"},
     *     summary="Danh sách thông tin Kho",
     *     description="Lấy danh sách kho theo các tiêu chí lọc hoặc phân trang.",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=false,
     *         description="Tên kho để tìm kiếm",
     *         @OA\Schema(
     *             type="string",
     *             example="Kho A"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *         required=false,
     *         description="Địa điểm kho để tìm kiếm",
     *         @OA\Schema(
     *             type="string",
     *             example="Hà Nội"
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
     *         description="Trang cần xem",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách kho trả về thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/WarehouseModel")
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
     *         description="Không tìm thấy kho phù hợp với tiêu chí",
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
        $data = $this->warehouseRepository->listAll($queryData);
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/warehouses/{id}",
     *     operationId="getWarehouse",
     *     tags={"Inventory"},
     *     summary="Chi tiết thông tin Kho",
     *     description="Lấy thông tin chi tiết của Kho theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của kho cần lấy thông tin",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin chi tiết của kho",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/WarehouseModel")
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
     *         description="Không tìm thấy kho với ID cung cấp",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy kho")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        try {
            $product = $this->warehouseRepository->findId($id);
            if (!$product) {
                return Result::fail(self::errMess);
            }
            return Result::success($product);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errMess);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/warehouses",
     *     operationId="createWarehouse",
     *     tags={"Inventory"},
     *     summary="Tạo kho mới",
     *     description="Thêm thông tin kho mới vào hệ thống.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="Tên kho", example="Kho B"),
     *             @OA\Property(property="location", type="string", description="Địa điểm kho", example="Hồ Chí Minh"),
     *             @OA\Property(property="warehouse_type", type="string", description="Loại kho", example="thành phẩm")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Kho mới được tạo thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/WarehouseModel")
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
     *         response=500,
     *         description="Lỗi trong quá trình xử lý",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Lỗi tạo kho")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'warehouse_type' => 'nullable|string|max:255',
        ]);
        try {
            $warehouse = $this->warehouseRepository->create($data);
            if (!$warehouse) {
                return Result::fail(self::errCreate);
            }
            return Result::success($warehouse);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errCreate);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/warehouses/{id}",
     *     operationId="updateWarehouse",
     *     tags={"Inventory"},
     *     summary="Cập nhật thông tin kho",
     *     description="Chỉnh sửa thông tin chi tiết của kho dựa trên ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của kho cần cập nhật",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="Tên kho", example="Kho Cập Nhật"),
     *             @OA\Property(property="location", type="string", description="Địa điểm kho", example="Hải Phòng"),
     *             @OA\Property(property="warehouse_type", type="string", description="Loại kho", example="nguyên vật liệu")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin kho đã được cập nhật thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/WarehouseModel")
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
     *         description="Không tìm thấy kho với ID cung cấp",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy kho")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi trong quá trình xử lý",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Lỗi cập nhật kho")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'warehouse_type' => 'nullable|string|max:255',
        ]);

        $product = $this->warehouseRepository->update($id, $data);
        if (!$product) {
            return Result::fail(self::errUpdate);
        }
        return Result::success($product);
    }

    /**
     * @OA\Delete(
     *     path="/api/warehouses/{id}",
     *     operationId="deleteWarehouse",
     *     tags={"Inventory"},
     *     summary="Xóa kho",
     *     description="Xóa thông tin kho theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của kho cần xóa",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa kho thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="boolean", description="Kết quả xóa", example=true)
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
     *         description="Không tìm thấy kho với ID cung cấp",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy kho")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi trong quá trình xử lý",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Lỗi xóa kho")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $product = $this->warehouseRepository->destroy($id);
            return Result::success($product);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errMess);
        }
    }
}
