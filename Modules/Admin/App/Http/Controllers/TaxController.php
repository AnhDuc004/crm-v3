<?php

namespace Modules\Admin\App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Result;
use Modules\Admin\Repositories\Tax\TaxInterface;
use Illuminate\Support\Facades\Log;

class TaxController extends Controller
{
    protected $taxRepository;
    const errorMess = 'Thuế không tồn tại';
    const errorCreateMess = "Thêm mới thuế thất bại";
    const errorUpdateMess = "Cập nhật thuế thất bại";
    const errorDeleteMess = "Xóa thuế thất bại";
    const successDeleteMess = 'Xoá thuế thành công';

    public function __construct(TaxInterface $taxRepository)
    {
        $this->taxRepository = $taxRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/tax",
     *     tags={"Admin"},
     *     summary="Lấy tất cả thuế",
     *     description="Lấy danh sách tất cả thuế.",
     *     operationId="getAllTax",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Tìm kiếm theo tên, tỷ lệ thuế",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Số bản ghi trên mỗi trang",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TaxModel")
     *         ),
     *         @OA\XmlContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TaxModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Giá trị thuế không hợp lệ"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $tax = $this->taxRepository->listAll($request->all());
        return Result::success($tax);
    }

    /**
     * @OA\Post(
     *     path="/api/tax",
     *     tags={"Admin"},
     *     summary="Tạo thuế mới",
     *     description="Tạo một thuế mới với các thông tin cung cấp.",
     *     operationId="createTax",
     *     @OA\RequestBody(
     *         description="Dữ liệu để tạo thuế mới",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/TaxModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/TaxModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo thuế thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TaxModel")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi xác thực dữ liệu"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        try {
            $data = $this->taxRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/tax/{id}",
     *     tags={"Admin"},
     *     summary="Lấy thông tin thuế theo ID",
     *     description="Lấy thông tin chi tiết của một thuế cụ thể theo ID.",
     *     operationId="getTax",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của thuế cần lấy thông tin",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin chi tiết thuế",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="data", ref="#/components/schemas/TaxModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy thuế"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        $data = $this->taxRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    /**
     * @OA\Put(
     *     path="/api/tax/{id}",
     *     tags={"Admin"},
     *     summary="Cập nhật thuế",
     *     description="Cập nhật thông tin của một thuế hiện có.",
     *     operationId="updateTax",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của thuế cần cập nhật",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         description="Dữ liệu cập nhật thuế",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/TaxModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/TaxModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thuế thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="data", ref="#/components/schemas/TaxModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy thuế"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi xác thực dữ liệu"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $this->taxRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorUpdateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tax/{id}",
     *     tags={"Admin"},
     *     summary="Xóa thuế",
     *     description="Xóa một thuế cụ thể theo ID.",
     *     operationId="deleteTax",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của thuế cần xóa",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thuế thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy thuế"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        $data = $this->taxRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
