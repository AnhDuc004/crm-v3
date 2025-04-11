<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Repositories\Supplier\SupplierInterface;
use App\Helpers\Result;
use Exception;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    protected $supplierRepository;

    const errMess = 'Nhà cung câp không tồn tại';
    const errUpdate = 'Cập nhật thất bại';
    const errDelete = 'Xóa thất bại';
    const errCreate = 'Thêm nhà cung cấp thất bại';

    public function __construct(SupplierInterface $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/suppliers",
     *     summary="Danh sách thông tin nhà cung cấp",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=0,
     *             description="Số bản ghi trả ra"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             description="Tìm theo tên nhà cung cấp"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách nhà cung cấp",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Supplier")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameters"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $data = $this->supplierRepository->listAll($queryData);
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/suppliers",
     *     summary="Tạo mới thông tin nhà cung cấp",
     *     tags={"Inventory"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="phone", type="number"),
     *             @OA\Property(property="email", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo mới thông tin nhà cung cấp",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        try {
            $supplier = $this->supplierRepository->create($request->all());
            if (!$supplier) {
                return Result::fail(self::errCreate);
            }
            return Result::success($supplier);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errCreate);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/suppliers/{id}",
     *     summary="Lấy thông tin chi tiết về thông tin nhà cung cấp theo ID",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             description="ID của thông tin nhà cung cấp"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chi tiết thông tin nhà cung cấp",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        try {
            $supplier = $this->supplierRepository->findId($id);
            if (!$supplier) {
                return Result::fail(self::errMess);
            }
            return Result::success($supplier);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errMess);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/suppliers/{id}",
     *     summary="Cập nhật thông tin nhà cung cấp",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID của thông tin nhà cung cấp"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="email", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update($id, Request $request)
    {
        try {
            $supplier = $this->supplierRepository->update($id, $request->all());
            if (!$supplier) {
                return Result::fail(self::errMess);
            }
            return Result::success($supplier);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errUpdate);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/suppliers/{id}",
     *     summary="Xóa thông tin nhà cung cấp",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Xóa thông tin nhà cung cấp theo Id"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thông tin nhà cung cấp thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Supplier deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $supplier = $this->supplierRepository->destroy($id);
            if (!$supplier) {
                return Result::fail(self::errMess);
            }
            return Result::success($supplier);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errDelete);
        }
    }
}
