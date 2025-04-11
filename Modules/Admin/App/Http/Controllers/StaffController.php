<?php

namespace Modules\Admin\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Result;
use Illuminate\Support\Facades\Log;
use Modules\Admin\Repositories\Staff\StaffInterface;
use \Exception;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    protected $staffRepository;

    // Error messages constants
    const ERROR_NOT_FOUND = 'Nhân viên không tồn tại';
    const ERROR_CHANGE_ACTIVE_FAILED = 'Thay đổi trạng thái thất bại';
    const ERROR_CREATE_FAILED = 'Tạo nhân viên thất bại';
    const ERROR_UPDATE_FAILED = 'Sửa nhân viên thất bại';
    const ERROR_DELETE_FAILED = 'Xóa nhân viên thất bại';
    const ERROR_DELETE_SUCCESS = 'Xóa nhân viên thành công';
    const ERROR_IMAGE_UPDATE = 'Cập nhật ảnh thất bại';

    // Validation rules and messages constants
    const VALIDATION_RULES = [
        'first_name' => 'bail|required|string|max:50',
        'last_name' => 'bail|required|string|max:50',
        'email' => 'bail|required|email|max:50|unique:staff,email',
        'facebook' => 'bail|nullable|string|max:100',
        'linkedin' => 'bail|nullable|string|max:100',
        'phone_number' => 'bail|nullable|string|regex:/^0([1-9]\d{8,9})$/',
        'skype' => 'bail|nullable|string|max:100',
        'admin' => 'bail|nullable|integer|in:0,1',
        'active' => 'bail|nullable|integer|in:0,1',
        'default_language' => 'bail|nullable|string|max:50',
        'direction' => 'bail|nullable|string|max:3',
        'is_not_staff' => 'bail|nullable|integer|in:0,1',
        'hourly_rate' => 'bail|nullable|numeric',
        'password' => 'bail|nullable|string|min:6|max:50',
    ];

    const VALIDATION_MESSAGES = [
        'first_name.*' => 'Tên không hợp lệ',
        'last_name.*' => 'Tên không hợp lệ',
        'email.*' => 'Email không hợp lệ hoặc đã tồn tại',
        'facebook.*' => 'Facebook không hợp lệ',
        'linkedin.*' => 'Linkedin không hợp lệ',
        'phone_number.*' => 'Điện thoại không hợp lệ',
        'skype.*' => 'Skype không hợp lệ',
        'admin.*' => 'Admin không hợp lệ',
        'active.*' => 'Active không hợp lệ',
        'default_language.*' => 'Ngôn ngữ không hợp lệ',
        'direction.*' => 'Direction không hợp lệ',
        'is_not_staff.*' => 'Nhân viên không hợp lệ',
        'hourly_rate.*' => 'Tiền công không hợp lệ',
        'password.*' => 'Mật khẩu không hợp lệ',
    ];

    public function __construct(StaffInterface $staffRepository)
    {
        $this->staffRepository = $staffRepository;
    }

    // Centralized error handling for exceptions
    private function handleException(Exception $e, $message)
    {
        Log::error($e->getMessage());
        return Result::fail($message);
    }

    /**
     * @OA\Get(
     *     path="/api/staff",
     *     summary="Lấy danh sách nhân viên",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Số lượng nhân viên mỗi trang (pagination)",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Tìm kiếm theo tên, email, số điện thoại",
     *         required=false,
     *         @OA\Schema(type="string", example="Nguyen")
     *     ),
     *     @OA\Parameter(
     *         name="order_name",
     *         in="query",
     *         description="Sắp xếp theo trường dữ liệu (mặc định: id)",
     *         required=false,
     *         @OA\Schema(type="string", example="first_name")
     *     ),
     *     @OA\Parameter(
     *         name="order_type",
     *         in="query",
     *         description="Kiểu sắp xếp (asc/desc, mặc định: desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="asc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách nhân viên được lấy thành công",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/StaffModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy dữ liệu"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        try {
            $staff = $this->staffRepository->listAll($request->all());
            return Result::success($staff);
        } catch (Exception $e) {
            Log::debug($e);
            return $this->handleException($e, self::ERROR_NOT_FOUND);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/staff",
     *     tags={"Admin"},
     *     summary="Tạo mới nhân viên",
     *     description="API này tạo mới nhân viên và hỗ trợ upload ảnh đại diện.",
     *     operationId="storeStaff",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"email", "first_name", "last_name", "password", "profile_image"},
     *                 @OA\Property(property="email", type="string", format="email", example="staff@example.com"),
     *                 @OA\Property(property="first_name", type="string", example="Nguyễn"),
     *                 @OA\Property(property="last_name", type="string", example="Văn A"),
     *                 @OA\Property(property="password", type="string", format="password", example="123456"),
     *                 @OA\Property(property="phone_number", type="string", example="0946547334"),
     *                 @OA\Property(
     *                     property="role", 
     *                     type="array", 
     *                     @OA\Items(type="integer", example=1), 
     *                     description="Danh sách các ID vai trò"
     *                 ),
     *                 @OA\Property(property="admin", type="integer", example="0"),
     *                 @OA\Property(property="profile_image", type="string", format="binary", description="Ảnh đại diện"),
     *                 @OA\Property(
     *                     property="department", 
     *                     type="array", 
     *                     @OA\Items(type="integer", example=1), 
     *                     description="Danh sách ID phòng ban"
     *                 ),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="array",
     *                     @OA\Items(type="string", example="30"),
     *                     description="Danh sách các quyền (permissions) của nhân viên"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Nhân viên được tạo thành công",
     *         @OA\JsonContent(ref="#/components/schemas/StaffModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid request"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::VALIDATION_RULES, self::VALIDATION_MESSAGES);

        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }

        try {
            return Result::success($this->staffRepository->create($request));
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return Result::fail(self::ERROR_CREATE_FAILED);
        }
    }

    // Show staff by ID
    /**
     * @OA\Get(
     *     path="/api/staff/{id}",
     *     tags={"Admin"},
     *     summary="Lấy thông tin chi tiết của nhân viên",
     *     description="Trả về thông tin của nhân viên dựa trên ID",
     *     operationId="getStaff_ById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của nhân viên",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="result",
     *                 type="object",
     *                 ref="#/components/schemas/StaffModel"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy nhân viên",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy nhân viên"),
     *             @OA\Property(property="errors", type="string", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ nội bộ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Đã xảy ra lỗi"),
     *             @OA\Property(property="errors", type="string", example="Exception message")
     *         )
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        try {
            $staff = $this->staffRepository->findId($id);

            // Log::debug($staff);
            if (!$staff) {
                return Result::fail(self::ERROR_NOT_FOUND);
            }
            return Result::success($staff);
        } catch (Exception $e) {
            Log::debug($e);
            return $this->handleException($e, self::ERROR_NOT_FOUND);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/staff/{id}",
     *     tags={"Admin"},
     *     summary="Cập nhật thông tin nhân viên",
     *     description="API này cập nhật thông tin nhân viên hiện có, bao gồm thông tin cá nhân, phòng ban, và quyền hạn.",
     *     operationId="updateStaff",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của nhân viên cần cập nhật",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="email", type="string", format="email", example="staff@example.com"),
     *                 @OA\Property(property="first_name", type="string", example="Nguyễn"),
     *                 @OA\Property(property="last_name", type="string", example="Văn A"),
     *                 @OA\Property(property="password", type="string", format="password", example="123456"),
     *                 @OA\Property(property="phone_number", type="string", example="0946547334"),
     *                 @OA\Property(property="roles", type="array", @OA\Items(type="integer", example=1)),
     *                 @OA\Property(property="admin", type="integer", example=0),
     *                 @OA\Property(property="department", type="array", @OA\Items(type="integer", example=1)),
     *                 @OA\Property(property="permissions", type="array", @OA\Items(type="string", example="30"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Cập nhật thành công"),
     *     @OA\Response(response=400, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=404, description="Không tìm thấy nhân viên"),
     *     @OA\Response(response=500, description="Lỗi server"),
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'nullable|string|min:6',
        ], [
            'first_name.required' => 'Tên không hợp lệ',
            'last_name.required' => 'Tên không hợp lệ',
            'email.required' => 'Email không hợp lệ',
            'email.email' => 'Email không đúng định dạng',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);

        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }

        try {
            return Result::success($this->staffRepository->update($id, $request));
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return Result::fail(self::ERROR_UPDATE_FAILED);
        }
    }


    // Delete staff by ID
    /**
     * @OA\Delete(
     *     path="/api/staff/{id}",
     *     summary="Xóa một nhân viên",
     *     description="Xóa một nhân viên bằng ID.",
     *     tags={"Admin"},
     *     security={{"bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của nhân viên cần xóa",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Xóa nhân viên thành công.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy nhân viên.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Lỗi hệ thống.")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $result = $this->staffRepository->destroy($id);
            if (!$result) {
                return Result::fail(self::ERROR_DELETE_FAILED);
            }
            return Result::success(self::ERROR_DELETE_SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->handleException($ex, self::ERROR_DELETE_FAILED);
        }
    }

    // Thay đổi trạng thái nhân viên
    /**
     * @OA\Put(
     *     path="/api/staff/{id}/toggle-active",
     *     tags={"Admin"},
     *     summary="Thay đổi trạng thái hoạt động của một nhân viên",
     *     description="Cập nhật trạng thái hoạt động của một Staff theo ID",
     *     operationId="toggleActiveByStaff",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của nhân viên muốn thay đổi trạng thái",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/StaffModel"),
     *         @OA\XmlContent(ref="#/components/schemas/StaffModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid staff ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Staff ID not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function toggleActive($id)
    {
        try {
            $staff = $this->staffRepository->toggleActive($id);
            return Result::success($staff);
        } catch (Exception $e) {
            return $this->handleException($e, self::ERROR_CHANGE_ACTIVE_FAILED);
        }
    }

    /**
     * Lấy danh sách nhân viên theo công việc
     * 
     * Phương thức này sẽ lấy danh sách các nhân viên dựa trên công việc mà họ được phân công.
     * Kết quả trả về sẽ là danh sách các nhân viên, bao gồm ID, tên và họ của nhân viên.
     * 
     * @OA\Get(
     *     path="/staff/list-by-task",
     *     summary="Lấy danh sách nhân viên theo công việc",
     *     description="Lấy danh sách nhân viên đã được phân công công việc.",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="request",
     *         in="query",
     *         description="Danh sách tham số cần thiết để lọc hoặc truy vấn",
     *         required=true,
     *         @OA\Schema(type="object", example={
     *             "task_id": 1,
     *             "status": "active"
     *         })
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách nhân viên theo công việc",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="ID nhân viên"),
     *                 @OA\Property(property="first_name", type="string", description="Tên nhân viên"),
     *                 @OA\Property(property="last_name", type="string", description="Họ nhân viên")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *     )
     * )
     */
    public function getListByTask(Request $request)
    {
        try {
            $staff = $this->staffRepository->getListByTask($request->all());
            return Result::success($staff);
        } catch (Exception $e) {
            return $this->handleException($e, self::ERROR_NOT_FOUND);
        }
    }

    /**
     * @OA\Get(
     *     path="/staff/list-by-ticket",
     *     summary="Lấy danh sách nhân viên theo vé",
     *     description="Lấy danh sách nhân viên đã được phân công các vé.",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="request",
     *         in="query",
     *         description="Danh sách tham số cần thiết để lọc hoặc truy vấn",
     *         required=true,
     *         @OA\Schema(type="object", example={
     *             "ticket_id": 1,
     *             "status": "open"
     *         })
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách nhân viên theo vé",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="ID nhân viên"),
     *                 @OA\Property(property="first_name", type="string", description="Tên nhân viên"),
     *                 @OA\Property(property="last_name", type="string", description="Họ nhân viên")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *     )
     * )
     */
    public function getListByTicket(Request $request)
    {
        try {
            $staff = $this->staffRepository->getListByTicket($request->all());
            return Result::success($staff);
        } catch (Exception $e) {
            return $this->handleException($e, self::ERROR_NOT_FOUND);
        }
    }

    // Retrieve staff by proposal
    public function getListByProposal(Request $request)
    {
        try {
            $staff = $this->staffRepository->getListByProposal($request->all());
            return Result::success($staff);
        } catch (Exception $e) {
            return $this->handleException($e, self::ERROR_NOT_FOUND);
        }
    }

    // Retrieve staff by estimate
    /**
     * @OA\Get(
     *     path="/api/staff/list-by-estimate/{staffId}",
     *     summary="Lấy danh sách ước tính liên quan đến nhân viên",
     *     description="Truyền vào ID nhân viên để lấy danh sách ước tính liên quan đến nhân viên đó.",
     *     operationId="getEstimatesByStaffId",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="staffId",
     *         in="path",
     *         description="ID của nhân viên",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách ước tính liên quan đến nhân viên",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 properties={
     *                     @OA\Property(property="id", type="integer", description="Mã ước tính", example=123),
     *                     @OA\Property(property="estimate_number", type="string", description="Số ước tính", example="EST-2025-001"),
     *                     @OA\Property(property="customer_name", type="string", description="Tên khách hàng", example="Nguyễn Văn A"),
     *                     @OA\Property(property="amount", type="number", format="float", description="Số tiền ước tính", example=1000000),
     *                     @OA\Property(property="status", type="string", description="Trạng thái ước tính", example="pending"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo", example="2025-01-23T10:00:00Z")
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid staff ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy dữ liệu",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Staff not found")
     *         )
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function getListByEstimate($staffId)
    {
        try {
            $staff = $this->staffRepository->getListByEstimate($staffId);
            return Result::success($staff);
        } catch (Exception $e) {
            return $this->handleException($e, self::ERROR_NOT_FOUND);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/staff/list-by-invoice/{staffId}",
     *     summary="Lấy danh sách hóa đơn liên quan đến nhân viên",
     *     description="Truyền vào ID nhân viên để lấy danh sách hóa đơn liên quan đến nhân viên đó.",
     *     operationId="getInvoicesByStaffId",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="staffId",
     *         in="path",
     *         description="ID của nhân viên",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách hóa đơn liên quan đến nhân viên",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 properties={
     *                     @OA\Property(property="id", type="integer", description="Mã hóa đơn", example=123),
     *                     @OA\Property(property="invoice_number", type="string", description="Số hóa đơn", example="INV-2025-001"),
     *                     @OA\Property(property="customer_name", type="string", description="Tên khách hàng", example="Nguyễn Văn A"),
     *                     @OA\Property(property="amount", type="number", format="float", description="Số tiền", example=500000),
     *                     @OA\Property(property="status", type="string", description="Trạng thái hóa đơn", example="paid"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo", example="2025-01-23T10:00:00Z")
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid staff ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy dữ liệu",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Staff not found")
     *         )
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function getListByInvoice($staffId)
    {
        try {
            $staff = $this->staffRepository->getListByInvoice($staffId);
            return Result::success($staff);
        } catch (Exception $e) {
            return $this->handleException($e, self::ERROR_NOT_FOUND);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/staff/{id}/profile-image",
     *     summary="Cập nhật ảnh đại diện nhân viên",
     *     tags={"Admin"},
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của nhân viên",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="profile_image",
     *                     type="string",
     *                     format="binary",
     *                     description="Ảnh đại diện của nhân viên"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật ảnh thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ảnh đại diện đã được cập nhật"),
     *             @OA\Property(property="profile_image", type="string", example="https://your-api.com/storage/images/profile/example.jpg")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Không có file ảnh nào được tải lên"),
     *     @OA\Response(response=404, description="Nhân viên không tồn tại"),
     *     @OA\Response(response=500, description="Lỗi server")
     * )
     */
    public function updateProfileImage(Request $request, $id)
    {
        $data = $this->staffRepository->updateProfileImage($request, $id);
        if (!$data) {
            return Result::fail(self::ERROR_IMAGE_UPDATE);
        }
        return Result::success($data);
    }
}
