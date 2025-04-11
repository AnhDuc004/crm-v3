<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Repositories\Material\MaterialInterface;

class MaterialController extends Controller
{
    protected $materialRepository;

    const errMess = 'Nguyên vật liệu không tồn tại';
    const errUpdate = 'Cập nhật thất bại';
    const errCreate = 'Thêm thất bại';

    public function __construct(MaterialInterface $materialRepository)
    {
        $this->materialRepository = $materialRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/materials",
     *     operationId="getAllMaterials",
     *     tags={"Inventory"},
     *     summary="Lấy danh sách nguyên vật liệu",
     *     description="Trả về danh sách các nguyên vật liệu với các tham số truy vấn (query parameters) để lọc dữ liệu.",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=false,
     *         description="Tên nguyên vật liệu để lọc",
     *         @OA\Schema(
     *             type="string",
     *             example="Sắt thép"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="unit_id",
     *         in="query",
     *         required=false,
     *         description="ID của đơn vị tính để lọc",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="supplier_id",
     *         in="query",
     *         required=false,
     *         description="ID của nhà cung cấp để lọc",
     *         @OA\Schema(
     *             type="integer",
     *             example=2
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy danh sách nguyên vật liệu thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MaterialModel"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu truy vấn không hợp lệ")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $materials = $this->materialRepository->getAll($queryData);
        return Result::success($materials);
    }

    /**
     * @OA\Get(
     *     path="/api/materials/{id}",
     *     operationId="showMaterial",
     *     tags={"Inventory"},
     *     summary="Lấy thông tin nguyên vật liệu theo ID",
     *     description="Trả về thông tin chi tiết của một nguyên vật liệu cụ thể.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của nguyên vật liệu",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy thông tin nguyên vật liệu thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/MaterialModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nguyên vật liệu không tồn tại",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Nguyên vật liệu không tìm thấy")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        try {
            $material = $this->materialRepository->findById($id);
            if (!$material) {
                return Result::fail(self::errMess);
            }
            return Result::success($material);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errMess);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/materials",
     *     summary="Thêm mới nguyên vật liệu",
     *     description="API để thêm mới một nguyên vật liệu vào hệ thống",
     *     tags={"Inventory"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "unit_id", "supplier_id"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 description="Tên nguyên vật liệu",
     *                 example="Xi măng Portland"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 nullable=true,
     *                 description="Mô tả nguyên vật liệu",
     *                 example="Xi măng dùng trong xây dựng nhà ở"
     *             ),
     *             @OA\Property(
     *                 property="unit_id",
     *                 type="integer",
     *                 description="ID của đơn vị tính",
     *                 example=3
     *             ),
     *             @OA\Property(
     *                 property="supplier_id",
     *                 type="integer",
     *                 description="ID của nhà cung cấp",
     *                 example=5
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/MaterialModel"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu đầu vào không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to create material")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_id' => 'required|exists:inv_units,id',
            'supplier_id' => 'required|exists:inv_suppliers,id',
        ]);
        try {
            $material = $this->materialRepository->create($data);
            if (!$material) {
                return Result::fail(self::errCreate);
            }
            return Result::success($material);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errCreate);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/materials/{id}",
     *     operationId="updateMaterial",
     *     tags={"Inventory"},
     *     summary="Cập nhật thông tin nguyên vật liệu",
     *     description="Cập nhật thông tin của nguyên vật liệu dựa trên ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của nguyên vật liệu cần cập nhật",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu cần cập nhật",
     *         @OA\JsonContent(
     *             required={"name", "unit_id", "supplier_id"},
     *             @OA\Property(property="name", type="string", description="Tên nguyên vật liệu", example="Sắt thép xây dựng"),
     *             @OA\Property(property="description", type="string", nullable=true, description="Mô tả nguyên vật liệu", example="Sắt thép dùng cho xây dựng"),
     *             @OA\Property(property="unit_id", type="integer", description="ID của đơn vị tính", example=2),
     *             @OA\Property(property="supplier_id", type="integer", description="ID của nhà cung cấp", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật nguyên vật liệu thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/MaterialModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy nguyên vật liệu",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Nguyên vật liệu không tìm thấy")
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
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_id' => 'required|exists:inv_units,id',
            'supplier_id' => 'required|exists:inv_suppliers,id',
        ]);
        $material = $this->materialRepository->update($id, $data);
        if (!$material) {
            return Result::fail(self::errUpdate);
        }
        return Result::success($material);
    }

    /**
     * @OA\Delete(
     *     path="/api/materials/{id}",
     *     operationId="destroyMaterial",
     *     tags={"Inventory"},
     *     summary="Xóa nguyên vật liệu",
     *     description="Xóa nguyên vật liệu dựa trên ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của nguyên vật liệu cần xóa",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa nguyên vật liệu thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", description="Thông tin nguyên vật liệu đã bị xóa")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy nguyên vật liệu",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Nguyên vật liệu không tìm thấy")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống khi xóa nguyên vật liệu",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không thể xóa nguyên vật liệu do lỗi hệ thống")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $material = $this->materialRepository->delete($id);
            return Result::success($material);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errMess);
        }
    }

    public function listSelect()
    {
        $data = $this->materialRepository->listSelect();
        return Result::success($data);
    }
}
