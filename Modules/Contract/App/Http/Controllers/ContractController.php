<?php

namespace Modules\Contract\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Contract\Repositories\Contract\ContractInterface;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Result;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Contract\Entities\Contract;

class ContractController extends Controller
{
    protected $contractRepository;
    const errorMess = 'Hợp đồng không tồn tại';
    const errorCreateMess = 'Tạo hợp đồng thất bại';
    const errorUpdateMess = 'Chỉnh sửa hợp đồng thất bại';
    const successDeleteMess = 'Xoá hợp đồng thành công';
    const errorDeleteMess = 'Xoá hợp đồng thất bại';
    const errCustomerMess = 'Khách hàng không tồn tại';
    const errorContentMess = 'Thay đổi nội dung thất bại';
    const errorCopyMess = 'Copy hợp đồng thất bại';
    const errorCountMess = 'Đếm số lượng hợp đồng hoạt động thất bại';
    const errCountContractType = 'Đếm số lượng theo loại hợp đồng thất bại';
    const errorUpdateComments = 'Cập nhật bình luận thất bại';
    const errorDeleteComments = 'Xóa bình luận thất bại';
    const errorCopyContract = 'Sap chép hợp đồng thất bại';

    public function __construct(ContractInterface $contractRepository)
    {
        $this->contractRepository = $contractRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/contract",
     *     tags={"Contract"},
     *     summary="Get all contracts",
     *     description="Retrieve a list of all contracts.",
     *     operationId="getAllContract",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by subject, datestart and dateend",
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
     *         description="Number of records per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ContractModel")
     *         ),
     *         @OA\XmlContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ContractModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid contract value"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $data = $request->all();
        $contract = $this->contractRepository->listAll($data);
        return Result::success($contract);
    }

    public function createByCustomer(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'bail|required|string|max:200',
            'description' => 'bail|nullable|string|max:500',
            'contract_type' => 'bail|nullable|integer|exists:contracts_types,id',
            'contract_value' => 'bail|nullable|numeric|min:0',
            'date_start' => 'bail|required|date',
            'dateend' => 'bail|nullable|date',
            'trash' => 'bail|nullable|integer|in:0,1',
            'not_visible_to_client' => 'bail|nullable|integer|in:0,1',

        ], [
            'subject.*' => 'Nội dung không quá 200 ký tự',
            'description.*' => 'Mô tả không quá 500 ký tự',
            'contract_type.*' => 'Chưa nhập nhóm hợp đồng',
            'contract_value.*' => 'Chưa nhập giá hợp đồng',
            'datestart.*' => 'Chưa nhập ngày bắt đầu',
            'date_end.*' => 'Chưa nhập ngày kết thúc',
            'trash.*' => 'Chưa chọn thùng giác',
            'not_visible_to_client.*' => 'Không hiển thị cho khách hàng',

        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->contractRepository->createByCustomer($id, $validator);
            if (!$data) {
                return Result::fail(static::errorCreateMess);
            }
            Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/contract/customer/{id}",
     *     summary="Lấy hợp đồng theo ID khách hàng",
     *     description="Truy xuất danh sách địa chỉ liên hệ được phân trang đến một khách hàng cụ thể.",
     *     operationId="getListByCustomer",
     *     tags={"Contract"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID khách hàng",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Giới hạn hồ sơ trả về",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Số trang để phân trang",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Thuật ngữ tìm kiếm để lọc hợp đồng",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ContractModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function getListByCustomer($id, Request $request)
    {
        $data = $this->contractRepository->getListByCustomer($id, $request);
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/contract/comment/{id}",
     *     tags={"Contract"},
     *     summary="Lấy danh sách bình luận theo ID hợp đồng",
     *     description="Lấy danh sách bình luận của hợp đồng theo ID cụ thể của hợp đồng",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id hợp đồng",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Số bản ghi trên 1 trang",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Số trang",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Số bản ghi lấy thành công",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ContractCommentsModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Tham số không đúng"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Hợp đồng không tồn tại"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ nội bộ"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function getListByComment($id, Request $request)
    {
        $data = $this->contractRepository->getListByComment($id, $request->all());
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/contract",
     *     tags={"Contract"},
     *     summary="Create a new Contract",
     *     description="Create a new Contract",
     *     operationId="createContract",
     *     @OA\RequestBody(
     *         description="Payload to create a new Contract",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/ContractModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/ContractModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contract created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ContractModel")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'bail|required|integer|exists:customers,id',
            'subject' => 'string|max:200',
            'description' => 'bail|nullable|string|max:500',
            'contract_type' => 'bail|nullable|integer|exists:contracts_types,id',
            'contract_value' => 'bail|nullable|numeric|min:0',
            'date_start' => 'bail|required|date',
            'date_end' => 'bail|nullable|date',
            'trash' => 'bail|nullable|integer|in:0,1',
            'not_visible_to_client' => 'bail|nullable|integer|in:0,1',
        ], [
            'customer_id.*' => 'Chưa nhập id khách hàng',
            'subject.*' => 'Nội dung không quá 200 ký tự',
            'description.*' => 'Mô tả không quá 500 ký tự',
            'contract_type.*' => 'Chưa nhập nhóm hợp đồng',
            'contract_value.*' => 'Chưa nhập giá hợp đồng',
            'date_start.*' => 'Chưa nhập ngày bắt đầu',
            'date_end.*' => 'Chưa nhập ngày kết thúc',
            'trash.*' => 'Chưa chọn thùng giác',
            'not_visible_to_client.*' => 'Không hiển thị cho khách hàng',
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $request->all();
            $contract = $this->contractRepository->create($data);
            if (!$contract) {
                return Result::fail(static::errorCreateMess);
            }
            return Result::success($contract);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/contract/{id}",
     *     tags={"Contract"},
     *     summary="Tìm hợp đồng theo ID",
     *     description="Tìm chi tiết hợp đồng theo ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Hợp đồng ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of contract",
     *         @OA\JsonContent(ref="#/components/schemas/ContractModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contract not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function show(Request $request, $id)
    {
        $contract = $this->contractRepository->findId($id);
        return Result::success($contract);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'bail|required|integer|exists:customers,id',
            'subject' => 'string|max:200',
            'description' => 'bail|nullable|string|max:500',
            'contract_type' => 'bail|nullable|integer|exists:contracts_types,id',
            'contract_value' => 'bail|nullable|numeric|min:0',
            'date_start' => 'bail|required|date',
            'date_end' => 'bail|nullable|date',
            'trash' => 'bail|nullable|integer|in:0,1',
            'not_visible_to_client' => 'bail|nullable|integer|in:0,1',
        ], [
            'customer_id.*' => 'Chưa nhập id khách hàng',
            'subject.*' => 'Nội dung không quá 200 ký tự',
            'description.*' => 'Mô tả không quá 500 ký tự',
            'contract_type.*' => 'Chưa nhập nhóm hợp đồng',
            'contract_value.*' => 'Chưa nhập giá hợp đồng',
            'date_start.*' => 'Chưa nhập ngày bắt đầu',
            'date_end.*' => 'Chưa nhập ngày kết thúc',
            'trash.*' => 'Chưa chọn thùng giác',
            'not_visible_to_client.*' => 'Không hiển thị cho khách hàng',
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        $data = $this->contractRepository->update($id, $request->all());
        return Result::success($data);
    }

    public function destroy($id)
    {
        $data = $this->contractRepository->destroy($id);
        if (!$data) {
            Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }

    public function countActive()
    {
        $data = $this->contractRepository->countActive();
        if (!$data) {
            return Result::fail(self::errorCountMess);
        }
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/contract/comment/{id}",
     *     summary="Tạo bình luận mới cho hợp đồng",
     *     description="API này cho phép tạo một bình luận mới cho hợp đồng bằng cách cung cấp nội dung bình luận.",
     *     operationId="createByComment",
     *     tags={"Contract"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của hợp đồng mà bạn muốn thêm bình luận vào",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *          )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Nội dung bình luận để thêm vào",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="content", type="string", example="Đây là một bình luận."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment successfully created",
     *         @OA\JsonContent(ref="#/components/schemas/ContractCommentsModel"),
     *         @OA\XmlContent(ref="#/components/schemas/ContractCommentsModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error_code", type="integer", example=1),
     *             @OA\Property(property="error_mess", type="string", example="Không thể tạo bình luận")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error_code", type="integer", example=2),
     *             @OA\Property(property="error_mess", type="string", example="Lỗi hệ thống")
     *         )
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function createByComment(Request $request, $id)
    {
        try {
            $contract = $this->contractRepository->findId($id);
            if (!$contract) {
                return Result::fail(self::errorMess);
            }
            $data = $this->contractRepository->createByComment($id, $request);
            return Result::success($data);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return Result::fail(self::errorMess);
        }
    }

    public function updateComment(Request $request, $id)
    {
        $data = $this->contractRepository->updateComment($id, $request->all());
        if (!$data) {
            return Result::fail(self::errorUpdateComments);
        }
        return Result::success($data);
    }

    public function destroyComment($id)
    {
        $data = $this->contractRepository->destroyComment($id);
        if (!$data) {
            return Result::fail(self::errorDeleteComments);
        }
        return Result::success($data);
    }

    public function countByContractType($id)
    {
        $data = $this->contractRepository->countByContractType($id);
        if (!$data) {
            return Result::fail(self::errCountContractType);
        }
        return Result::success($data);
    }

    public function filterByContract(Request $request)
    {
        $data = $this->contractRepository->filterByContract($request->all());
        return Result::success($data);
    }

    public function statisticContractsByType()
    {
        $data = $this->contractRepository->statisticContractsByType();
        return Result::success($data);
    }

    public function statisticContractsValueByType()
    {
        $data = $this->contractRepository->statisticContractsValueByType();
        return Result::success($data);
    }

    public function contractByContent($id, Request $request)
    {
        $data = $this->contractRepository->contractByContent($id, $request->all());
        return Result::success($data);
    }

    public function changeSigned($id, Request $request)
    {
        $data = $this->contractRepository->changeSigned($id, $request->all());
        return Result::success($data);
    }

    public function copyContract($id)
    {
        $data = $this->contractRepository->copyContract($id);
        if (!$data) {
            return Result::fail(self::errorCopyContract);
        }
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/contract/count",
     *     summary="Lấy số lượng hợp đồng theo loại",
     *     description="Endpoint này trả về danh sách số lượng hợp đồng theo từng loại hợp đồng.",
     *     tags={"Contract"},
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ContractModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ nội bộ"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function countContractsByType()
    {
        $data = $this->contractRepository->countContractsByType();
        return Result::success($data);
    }
}
