<?php

namespace Modules\Project\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Project\Entities\ProjectFiles;
use Modules\Project\Entities\ProjectMember;
use Modules\Project\Repositories\ProjectFiles\ProjectFilesInterface;
use Illuminate\Support\Facades\Log;

class ProjectFilesController extends Controller
{
    protected $ProjectFilesRepository;
    const errorCreateMess = 'Tạo file thất bại';
    const errorMess = 'file không tồn tại';
    const errorDeleteMess = 'Xóa file thất bại';
    const errorUpdateMess = 'Sửa file thất bại';
    const errorChangeActive = 'Thay đổi trạng thái thất bại';

    public function __construct(ProjectFilesInterface $ProjectFilesRepository)
    {
        $this->ProjectFilesRepository = $ProjectFilesRepository;
    }

    public function index(Request $request)
    {
        $data = $this->ProjectFilesRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            // 'name' => 'bail|required|string',
            // 'due_date' => 'bail|required|date',
            // //'project_id' => 'bail|required|integer',
        ], [
            // 'name.*' => 'Bạn chưa nhập tên cột mốc',
            // 'due_date.*' => 'Bạn chưa nhập ngày chốt',
            // //'project_id.required' => 'Bạn chưa nhập dự án',

        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->ProjectFilesRepository->create($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function destroy($id)
    {
        $data = $this->ProjectFilesRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }

    public function update($id, Request $request)
    {
        try {
            $data = $this->ProjectFilesRepository->update($id, $request->all());
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
     * @OA\Post(
     *     path="/api/file/project/{project_id}",
     *     summary="Tải tệp lên dự án",
     *     description="Thêm một tệp mới vào dự án cụ thể. Tệp được lưu trong thư mục `storage/app/public/uploads/`.",
     *     operationId="uploadFileByProject",
     *     tags={"Project"},
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         required=true,
     *         description="ID của dự án",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="file_name",
     *                     type="string",
     *                     format="binary",
     *                     description="Tệp cần upload"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     description="Mô tả tệp",
     *                     example="Nhập mô tả"
     *                 ),
     *                 @OA\Property(
     *                     property="subject",
     *                     type="string",
     *                     description="Chủ đề của tệp",
     *                     example="Chọn chủ đề tệp"
     *                 ),
     *                 @OA\Property(
     *                     property="contact_email",
     *                     type="string",
     *                     description="Email liên hệ",
     *                     example="email@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="visible_to_customer",
     *                     type="integer",
     *                     description="Hiển thị cho khách hàng",
     *                     example="1"
     *                 ),
     *                 @OA\Property(
     *                     property="external",
     *                     type="string",
     *                     description="Ngoại vi (độ dài tùy ý)",
     *                     example="External Data"
     *                 ),
     *                 @OA\Property(
     *                     property="external_link",
     *                     type="string",
     *                     description="Liên kết ngoại vi (tùy chọn)",
     *                     example="http://example.com/file"
     *                 ),
     *                 @OA\Property(
     *                     property="thumbnail_link",
     *                     type="string",
     *                     description="Liên kết hình thu nhỏ (tùy chọn)",
     *                     example="http://example.com/thumbnail.jpg"
     *                 ),
     *                 required={"file_name","contact_email"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectFileModel"),
     *         @OA\XmlContent(ref="#/components/schemas/ProjectFileModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu yêu cầu không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Đã xảy ra lỗi không mong muốn")
     *         )
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function uploadFileByProject($id, Request $request)
    {
        return Result::success($this->ProjectFilesRepository->uploadFileByProject($id, $request->all()));
    }

    /**
     * @OA\Get(
     *     path="/api/file/project/{id_project}",
     *     tags={"Project"},
     *     summary="Get a specific file project by ID",
     *     description="Retrieve details of a specific file project by its ID.",
     *     operationId="getFileProjectById",
     *     @OA\Parameter(
     *         name="id_project",
     *         in="path",
     *         description="id_project of the table projectFile to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by file_name",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
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
     *         @OA\JsonContent(ref="#/components/schemas/ProjectFileModel"),
     *         @OA\XmlContent(ref="#/components/schemas/ProjectFileModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File project not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByProject($id, Request $request)
    {
        try {
            $fileProject = $this->ProjectFilesRepository->getListByProject($id, $request->all());
            if (!$fileProject) {
                return Result::fail(self::errorMess);
            }
            return Result::success($fileProject);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorMess);
        }
    }

    public function changeVisibleToCustomer($id, Request $request)
    {
        return Result::success($this->ProjectFilesRepository->changeVisibleToCustomer($id, $request->all()));
    }

    public function download($id)
    {
        $file = ProjectFiles::find($id);
        $filePath = public_path("/storage/uploads/" . $file->file_name);
        $headers = ['Content-Type'];
        $fileName = time();
        return response()->download($filePath, $fileName, $headers);
    }
}
