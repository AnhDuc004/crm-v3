<?php

namespace Modules\Lead\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Lead\Repositories\Lead\LeadInterface;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Customer\Entities\Tag;
use PhpParser\Node\Stmt\Return_;

class LeadController extends Controller
{
    protected $leadRepository;

    const MESSAGE_SUCCESS_CREATE = 'Tạo khách hàng thành công';
    const messageCodeError = 'Khách hàng không tồn tại';
    const canNotDelete = 'Không thể xóa khách hàng';
    const MESSAGE_ERROR = 'Khách hàng không tồn tại';
    const MESSAGE_DELETE_ERROR = 'Không thể xóa khách hàng';
    const MESSAGE_CREATE_ERROR = 'Tạo khách hàng thất bại';
    const MESSAGE_UPDATE_ERROR = 'Cập nhật khách hàng thất bại';
    const MESSAGE_DELETE_SUCCESS = 'Xóa khách hàng thành công';
    const MESSAGE_DELETE_ERORR = 'Xóa khách hàng thất bại';
    const MESSAGE_CONVERT_SUCCESS = 'Đã convert thành công';
    const MESSAGE_COUNT_ERROR = 'Đếm trạng thái hoạt động thất bại';
    const MESSAGE_STATUS_ERORR = 'Thay đổi trạng thái thất bại';

    public function __construct(LeadInterface $leadRepository)
    {
        $this->leadRepository = $leadRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/lead",
     *     summary="Lấy danh sách các leads",
     *     security={{"bearer": {}}},
     *     tags={"Lead"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Số lượng kết quả trên mỗi trang",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Số trang",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="assigned",
     *         in="query",
     *         required=false,
     *         description="ID của nhân viên được giao",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Trạng thái của lead",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         required=false,
     *         description="Nguồn lead",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Tìm kiếm theo tên, email, số điện thoại",
     *         @OA\Schema(type="string", example="John Doe")
     *     ),
     *     @OA\Parameter(
     *         name="orderName",
     *         in="query",
     *         required=false,
     *         description="Trường sắp xếp",
     *         @OA\Schema(type="string", example="id")
     *     ),
     *     @OA\Parameter(
     *         name="orderType",
     *         in="query",
     *         required=false,
     *         description="Kiểu sắp xếp (asc, desc)",
     *         @OA\Schema(type="string", example="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách leads",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Danh sách lead"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/LeadModel")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function index(Request $request)
    {
        try {
            $leads = $this->leadRepository->listAll($request->all());
            return Result::success($leads);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::MESSAGE_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/lead",
     *     tags={"Lead"},
     *     summary="Tạo khách hàng tiềm năng mới",
     *     description="Tạo một khách hàng tiềm năng mới trong hệ thống.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LeadModel")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Khách hàng tiềm năng đã được tạo thành công",
     *         @OA\JsonContent(ref="#/components/schemas/LeadModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu đầu vào không hợp lệ"
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:191',
            'source' => 'bail|required|numeric',
            'title' => 'bail|nullable|string|max:500',
        ], [
            'name.required' => 'Chưa nhập tên khách hàng',
            'name.max' => 'Tên khách không quá 191 ký tự',
            'source.required' => 'Source chưa nhập',
            'source.numeric' => 'Source là số >= 0',
            'title.max' => 'Tiêu đề không quá 500 ký tự',
        ]);

        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $lead = $this->leadRepository->create($request->all());
            return Result::success($lead);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::MESSAGE_CREATE_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/lead/{id}",
     *     tags={"Lead"},
     *     summary="Lấy thông tin khách hàng tiềm năng theo ID",
     *     description="Truy xuất chi tiết của một khách hàng tiềm năng theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của khách hàng tiềm năng",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Khách hàng tìm thấy",
     *         @OA\JsonContent(ref="#/components/schemas/LeadModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy khách hàng"
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function show($id, Request $request)
    {
        try {
            $data = $this->leadRepository->findId($id, $request->all());
            if (!$data) {
                return Result::fail(self::messageCodeError);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageCodeError);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/lead/{id}",
     *     tags={"Lead"},
     *     summary="Update khách hàng tiềm năng mới",
     *     description="Cập nhật một khách hàng tiềm năng mới trong hệ thống.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the task to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LeadModel")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Khách hàng tiềm năng đã được tạo thành công",
     *         @OA\JsonContent(ref="#/components/schemas/LeadModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu đầu vào không hợp lệ"
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:191',
            'dateadded' => 'bail|nullable|date',
            'source' => 'bail|required|numeric',
            'description' => 'bail|nullable|string|max:500',
        ], [
            'name.required' => 'Chưa nhập tên khách hàng',
            'name.max' => 'Tên khách không quá 191 ký tự',
            'dateadded.date' => 'Ngày không đúng định dạng',
            'source.required' => 'Source chưa nhập',
            'source.numeric' => 'Source là số >= 0',
            'description.max' => 'Mô tả không quá 500 ký tự',
        ]);

        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }

        DB::beginTransaction();
        try {
            $lead = $this->leadRepository->update($id, $request->all());
            Log::debug($lead);
            if (!$lead) {
                return Result::fail(self::messageCodeError);
            }
            DB::commit();
            return Result::success($lead);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return Result::fail(self::MESSAGE_UPDATE_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/lead/{id}",
     *     tags={"Lead"},
     *     summary="Delete a lead",
     *     description="Delete a specific lead by its ID.",
     *     operationId="deleteLead",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the lead to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lead deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid lead ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lead not found"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        $lead = $this->leadRepository->destroy($id);
        if(!$lead){
            return Result::fail(self::MESSAGE_DELETE_ERORR);
        }
        return Result::success($lead);
    }

    /**
     * @OA\Put(
     *     path="/api/lead/{lead_id}/{status_id}",     
     *     tags={"Lead"},
     *     summary="Thay đổi trạng thái khách hàng",
     *     description="Thay đổi trạng thái của khách hàng",
     *     @OA\Parameter(
     *         name="lead_id",
     *         in="path",
     *         required=true,
     *         description="ID của khách hàng",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status_id",
     *         in="path",
     *         required=true,
     *         description="Status Id mới",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật trạng thái khách hàng thành công"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ"
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function changeStatus($id, $status)
    {
        try {
            return Result::success($this->leadRepository->changeStatus($id, $status));
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return Result::fail(self::MESSAGE_STATUS_ERORR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/lead/convert-to-customer/{id}",
     *     summary="Chuyển đổi Lead thành Customer",
     *     tags={"Lead"},
     *     description="API này dùng để chuyển đổi thông tin Lead thành Customer",
     *     operationId="convertLeadToCustomer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của Lead cần chuyển đổi",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu để chuyển đổi Lead thành Customer",
     *         @OA\JsonContent(ref="#/components/schemas/LeadModel")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chuyển đổi thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Lead đã được chuyển đổi thành công."),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/CustomerModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lead không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Lead không tồn tại.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ.")
     *         )
     *     ),
     *     security={{"bearer": {}}
     *     }
     * )
     */
    public function convertToCustomer($id, Request $request)
    {
        try {
            $data = $this->leadRepository->convertToCustomer($id, $request->all());
            if (!$data) {
                return Result::fail(self::MESSAGE_ERROR);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::MESSAGE_ERROR);
        }
    }

    public function countLeadBySources($id)
    {
        $data = $this->leadRepository->countLeadBySources($id);
        if(!$data){
            return Result::fail(self::MESSAGE_COUNT_ERROR);
        }
        return Result::success($data);
    }

    public function countLeadByStatus($id)
    {
        $data = $this->leadRepository->countLeadByStatus($id);
        if(!$data){
            return Result::fail(self::MESSAGE_COUNT_ERROR);
        }
        return Result::success($data);
    }
}
