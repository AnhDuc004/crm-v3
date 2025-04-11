<?php

namespace Modules\Contract\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Contract\Entities\Contract;
use Modules\Contract\Entities\ContractRenewals;
use Modules\Contract\Repositories\ContractRenewals\ContractRenewalsInterface;

class ContractRenewalsController extends Controller
{
    const errorMess = 'Gia hạn hợp đồng không tồn tại';
    const errorCreateMess = 'Tạo gia hạn hợp đồng thất bại';
    const errorUpdateMess = 'Chỉnh sửa gia hạn hợp đồng thất bại';
    const successDeleteMess = 'Xoá gia hạn hợp đồng thành công';
    const errorDeleteMess = 'Xoá gia hạn hợp đồng thất bại';
    protected $contractTypeRepository;

    public function __construct(ContractRenewalsInterface $contractTypeRepository)
    {
        $this->contractTypeRepository = $contractTypeRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/contract/renewals",
     *     tags={"Contract"},
     *     summary="Lấy danh sách gia hạn hợp đồng",
     *     description="Lấy danh sách tất cả các gia hạn hợp đồng với các tham số lọc tùy chọn.",
     *     operationId="listAllContractRenewals",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Từ khóa tìm kiếm",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Số lượng bản ghi trên mỗi trang",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ContractRenewals"))
     *         )
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function index(Request $request)
    {
        $data = $this->contractTypeRepository->listAll($request->all());
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/contract/renewal/{id}/",
     *     summary="Tạo hợp đồng gia hạn mới",
     *     description="Lưu trữ các hợp đồng gia hạn mới tạo vào bộ nhớ.",
     *     operationId="createContractRenewal",
     *     tags={"Contract"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id hợp đồng",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="new_start_date", type="string", format="date", description="New start date", example="2023-01-01"),
     *             @OA\Property(property="new_end_date", type="string", format="date", description="New end date", example="2023-12-31"),
     *             @OA\Property(property="new_value", type="number", format="float", description="New contract value", example="1000.00"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully created",
     *         @OA\JsonContent(ref="#/components/schemas/ContractRenewals")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="không tìm thấy khách hàng hoặc hợp đồng"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function create($id, Request $request)
    {
        try {
            $data = $this->contractTypeRepository->create($id, $request);
            return Result::success($data);
        } catch (Exception $e) {
            return Result::fail(self::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/contract/renewal/detail/{id}",
     *     tags={"Contract"},
     *     summary="Lấy chi tiết gia hạn hợp đồng",
     *     description="Lấy thông tin chi tiết của một gia hạn hợp đồng theo ID.",
     *     operationId="getContractRenewalDetail",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của gia hạn hợp đồng",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="data", ref="#/components/schemas/ContractRenewals")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy gia hạn hợp đồng"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function show($id)
    {
        $data = $this->contractTypeRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    /**
     * @OA\Put(
     *     path="/api/contract/renewal/{id}",
     *     tags={"Contract"},
     *     summary="Cập nhật gia hạn hợp đồng",
     *     description="Cập nhật thông tin của một gia hạn hợp đồng đã tồn tại.",
     *     operationId="updateContractRenewal",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của gia hạn hợp đồng cần cập nhật",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="new_start_date", type="string", format="date", description="Ngày bắt đầu mới", example="2023-01-01"),
     *             @OA\Property(property="new_end_date", type="string", format="date", description="Ngày kết thúc mới", example="2023-12-31"),
     *             @OA\Property(property="new_value", type="number", format="float", description="Giá trị hợp đồng mới", example="1000.00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="data", ref="#/components/schemas/ContractRenewals")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy gia hạn hợp đồng"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'new_start_date' => 'bail|required|date',
            'new_value' => 'bail|nullable|numeric|min:0',
            'new_end_date' => 'bail|nullable|date',

        ], [
            'new_start_date.*' => 'Chưa nhập ngày bắt đầu',
            'new_end_date.*' => 'Chưa nhập ngày kết thúc',
            'new_value.*' => 'Chưa nhập giá trị',

        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        return $this->contractTypeRepository->update($id, $request->all());
    }

    /**
     * @OA\Delete(
     *     path="/api/contract/renewal/{id}",
     *     summary="Xóa báo giá",
     *     description="Xóa báo giá theo ID",
     *     tags={"Contract"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID báo giá muốn xóa"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa báo giá thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contract deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Xóa báo giá thất bại",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Failed to delete the contract")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ nội bộ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="An error occurred while deleting the contract")
     *         )
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function destroy($id)
    {
        try {
            $data = $this->contractTypeRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::errorDeleteMess);
            }
            return Result::success($data);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return Result::fail($ex->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/contract/renewal/{id}",
     *     tags={"Contract"},
     *     summary="Lấy danh sách gia hạn theo hợp đồng",
     *     description="Lấy danh sách gia hạn cho một hợp đồng cụ thể.",
     *     operationId="getListByContract",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của hợp đồng",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách gia hạn hợp đồng",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ContractRenewals"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy hợp đồng"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function getListByContract($id, Request $request)
    {
        $data = $this->contractTypeRepository->getListByContract($id, $request->all());
        return Result::success($data);
    }
}
