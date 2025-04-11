<?php

namespace Modules\Project\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Project\Repositories\Project\ProjectInterface;
use App\Helpers\Result;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    protected $projectRepository;
    const errorMess = 'Dự án không tồn tại';
    const errorCreateMess = 'Tạo dự án thất bại';
    const errorCopyMess = 'Copy dự án thất bại';
    const errorUpdateMess = 'Cập nhật dự án thất bại';
    const errorDeleteMess = 'Xóa dự án thất bại';
    const errCustomerMess = "Khách hàng không tồn tại";
    const errorCreateMember = "Thêm thành viên thất bại";
    const errorDeleteMember = "Xóa thành viên thất bại";
    const errorBulkAction = "Thay đổi hàng loạt thất bại";
    const successBulkAction = "Thay đổi hàng loạt thành công";

    public function __construct(ProjectInterface $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * @OA\Get(
     *      path="/api/project",
     *      tags={"Project"},
     *      summary="Danh sách Project",
     *      description="Lấy danh sách Project",
     *      operationId="getProject",
     *      @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          description="Giới hạn phần tử theo trang",
     *          required=false,
     *          explode=true,
     *          @OA\Schema(
     *              default="10",
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="Cụm từ tìm kiếm theo tên dự án",
     *          required=false,
     *          explode=true,
     *          @OA\Schema(
     *              default="",
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Trạng thái dự án: là danh sách trạng thái [1,2,3]",
     *          required=false,
     *          explode=true,
     *          @OA\Schema(
     *              default="[1,2,3]",
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="by",
     *          in="query",
     *          description="Trường dùng để lọc theo bảng: customer/staff/..., nếu có nhiều lựa chọn sẽ thêm vào đây",
     *          required=false,
     *          explode=true,
     *          @OA\Schema(
     *              default="customer",
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="byId",
     *          in="query",
     *          description="Trường dùng để lọc theo bảng: customer/staff/..., nếu có nhiều lựa chọn sẽ thêm vào đây",
     *          required=false,
     *          explode=true,
     *          @OA\Schema(
     *              default="1",
     *              type="int"
     *          )
     *      ),
     *      security={{"bearer":{}}},
     *      @OA\Response(
     *          response="200", 
     *          description="Lấy danh sách dự án project."
     *      )
     * )
     */
    public function index(Request $request)
    {
        try {
            $project = $this->projectRepository->listAll($request->all());
            return Result::success($project);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorMess);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/project",
     *     tags={"Project"},
     *     summary="Tạo mới project",
     *     description="Hàm dùng tạo mới dự án",
     *     operationId="addProject",
     *     @OA\RequestBody(
     *         description="Create a new Project",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/ProjectModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/ProjectModel")
     *             )
     *         }
     *     ),
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectModel"),
     *         @OA\XmlContent(ref="#/components/schemas/ProjectModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation exception"
     *     ),
     * )
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $project = $this->projectRepository->create($data);
            return Result::success($project) ?? Result::fail(self::errorCreateMess);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/project/{id}",
     *     tags={"Project"},
     *     summary="Hiển thị project theo id",
     *     description="Hàm lấy project theo id.",
     *     operationId="getProjectById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của Project",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectModel"),
     *         @OA\XmlContent(ref="#/components/schemas/ProjectModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     * )
     */
    public function show($id)
    {
        return $this->projectRepository->findId($id);
    }

    /**
     * @OA\Put(
     *     path="/api/project/{id}",
     *     tags={"Project"},
     *     summary="Chỉnh sửa Project",
     *     description="Hàm chỉnh sửa Project",
     *     operationId="updateProject",
     *         @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của Project",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *        ),    
     *     @OA\RequestBody(
     *         description="Dữ liệu gửi kèm trong body",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/ProjectModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/ProjectModel")
     *             )
     *         }
     *     ),
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectModel"),
     *         @OA\XmlContent(ref="#/components/schemas/ProjectModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Mô tả lỗi TBD.."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Mô tả lỗi TBD.."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Mô tả lỗi TBD.."
     *     ),
     * )
     */
    public function update($id, Request $request)
    {
        try {
            $data = $request->all();
            $project = $this->projectRepository->update($id, $data);
            return Result::success($project) ?? Result::fail(self::errorUpdateMess);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/project/{id}",
     *     tags={"Project"},
     *     summary="Xóa Project",
     *     description="Hàm dùng để xóa Project",
     *     operationId="deleteProject",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of task to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=400,
     *         description="Mô tả lỗi TBD.."
     *     ),
     * )
     */
    public function destroy($id)
    {
        try {
            $project = $this->projectRepository->destroy($id);
            return Result::success($project) ?? Result::fail(self::errorDeleteMess);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorDeleteMess);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/project/copy/{id}",
     *     tags={"Project"},
     *     summary="Copy a project",
     *     description="Copies an existing project.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the project to copy",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Project copied successfully",
     *         @OA\Property(ref="#/components/schemas/Project")
     *     ),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=404, description="Project not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function copy($id, Request $request)
    {
        $project =  $this->projectRepository->copy($id, $request->all());
        if (!$project)
            return Result::fail(self::errorCopyMess);
        return Result::success($project);
    }

    /**
     * @OA\Get(
     *      path="/api/project/customer/{id}",
     *      tags={"Project"},
     *      summary="DEPRECATE: Danh sách Project theo customerID",
     *      description="Hàm lấy danh sách project theo customer ID, nhưng hàm đã DEPRECATE do trùng với hàm getALL. Hãy sử dụng hàm GET /api/project với điều kiện by=customer&byID={id}",
     *      operationId="getProjectbyCustomerID",
     *      @OA\Response(
     *          response=400,
     *          description="Mô tả lỗi TBD.."
     *      ),
     * )
     */
    public function getListByCustomer($id, Request $request)
    {
        return $this->projectRepository->getListByCustomer($id, $request->all());
    }

    // hàm này thực chất là hàm creat a project
    // tạm thời comment chờ phản hồi từ FE sau đó sẽ xóa
    // public function createByCustomer($id, Request $request)
    // {
    //     return $this->projectRepository->createByCustomer($id, $request->all());
    // }

    /**
     * @OA\Get(
     *     path="/api/project/customer/count/{id}",
     *     tags={"Project"},
     *     summary="Đếm dự án theo trạng thái dự án, lọc theo customer_id. TBD: Hàm nên đổi tên thành getProjectSumary",
     *     description="Hàm đếm .",
     *     operationId="getCountByCustomerId",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của Project",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectModel"),
     *         @OA\XmlContent(ref="#/components/schemas/ProjectModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     * )
     */
    public function countByCustomer($id)
    {
        return $this->projectRepository->countByCustomer($id);
    }

    /**
     * @OA\Get(
     *      path="/api/project/staff/{id}",
     *      tags={"Project"},
     *      summary="DEPRECATE: Danh sách Project theo staffID",
     *      description="Hàm lấy danh sách project theo staff ID, nhưng hàm đã DEPRECATE do trùng với hàm getALL. Hãy sử dụng hàm GET /api/project với điều kiện by=staff&byID={id}",
     *      operationId="getProjectbyStaffID",
     *      @OA\Response(
     *          response=400,
     *          description="Mô tả lỗi TBD.."
     *      ),
     * )
     */
    public function getListByStaff($id, Request $request)
    {
        return Result::success($this->projectRepository->getListByStaff($id, $request->all()));
    }

    public function countOverview($id)
    {
        return Result::success($this->projectRepository->countOverview($id));
    }

    public function countDayLeft($id)
    {
        return Result::success($this->projectRepository->countDayLeft($id));
    }

    public function countByStatus()
    {
        return Result::success($this->projectRepository->countByStatus());
    }

    public function getContactByProject($id)
    {
        return Result::success($this->projectRepository->getContactByProject($id));
    }

    /**
     * @OA\Post(
     *     path="/api/project/staff/{id}",
     *     summary="Thêm thành viên vào dự án",
     *     description="Thêm thành viên vào dự án dựa trên ID của dự án.",
     *     tags={"Project"},
     *     security={{"bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của dự án cần thêm thành viên",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="members", type="array", description="Danh sách thành viên", 
     *                 @OA\Items(
     *                     @OA\Property(property="staff_id", type="integer", description="ID của nhân viên", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thêm thành viên thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Thành viên đã được thêm vào dự án thành công."),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/ProjectModel")
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
     *             @OA\Property(property="message", type="string", example="Không tìm thấy dự án.")
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
    public function addMember($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $project = $this->projectRepository->addMember($id, $request->all());

            if (isset($project['error'])) {
                DB::rollback();
                return Result::fail($project['error']);
            }

            DB::commit();
            return Result::success($project);
        } catch (Exception $ex) {
            DB::rollback();
            Log::error($ex->getMessage());
            return Result::fail(static::errorCreateMember);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/project/{project_id}/staff/{staff_id}",
     *     tags={"Project"},
     *     summary="Xóa thành viên ra khỏi dự án",
     *     description="Xóa thành viên khỏi dự án",
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         description="ID dự án",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="staff_id",
     *         in="path",
     *         description="ID của thành viên muốn xóa",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="ID không hợp lệ!"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Thành viên không tồn tại"
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function deleteMember($project_id, $staff_id)
    {
        DB::beginTransaction();
        try {
            $result = $this->projectRepository->destroyMember($project_id, $staff_id);
            $data = Result::success($result);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            $data = Result::fail(self::errorDeleteMember);
        }
        return Result::success($data);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'project_ids' => 'required|array',
            'project_ids.*' => 'exists:projects,id',
            'action' => 'required|string|in:delete,update_status,update_creator',
            'status' => 'nullable|integer',
            'updated_by' => 'nullable|exists:users,id',
            'created_by' => 'nullable|exists:users,id'
        ]);

        Log::info('Received bulk action request:', $request->all()); // Log toàn bộ request

        try {
            $this->projectRepository->bulkAction($request->all());
            return Result::success(self::successBulkAction);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorBulkAction);
        }
    }
}
