<?php

namespace Modules\Tik\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Tik\Repositories\TikFile\TikFileInterface;
use Illuminate\Support\Facades\Validator;

class TikFileController extends Controller
{
    protected $tikFileRepository;

    const errMess = 'File không tồn tại';
    const errCreate = 'Thêm thất bại';
    const errValidate = 'Dữ liệu không hợp lệ';
    const errSystem = 'Lỗi hệ thống';
    const errUpdate = 'Cập nhật thất bại';
    const errDelete = 'Xóa thất bại';
    public function __construct(TikFileInterface $tikFileRepository)
    {
        $this->tikFileRepository = $tikFileRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/tik-files",
     *     summary="Danh sách file",
     *     description="Returns paginated list of files with optional filtering",
     *     operationId="tikFilesList",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="file_name",
     *         in="query",
     *         description="Filter by file name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of records per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/TikFile")),
     *                 @OA\Property(property="first_page_url", type="string", example="http://example.com/api/tik-files?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="last_page_url", type="string", example="http://example.com/api/tik-files?page=3"),
     *                 @OA\Property(property="next_page_url", type="string", example="http://example.com/api/tik-files?page=2"),
     *                 @OA\Property(property="path", type="string", example="http://example.com/api/tik-files"),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="prev_page_url", type="string", example=null),
     *                 @OA\Property(property="to", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=25)
     *             ),
     *             @OA\Property(property="message", type="string", example="Files retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while retrieving files")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $files = $this->tikFileRepository->getAll($queryData);
        return Result::success($files);
    }

    /**
     * @OA\Get(
     *     path="/api/tik-files/{id}",
     *     summary="Lấy thông tin file theo ID",
     *     description="Returns a specific file by ID",
     *     operationId="tikFilesShow",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of file to return",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/TikFile"),
     *             @OA\Property(property="message", type="string", example="File retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="File not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $file = $this->tikFileRepository->findById($id);

        if (!$file) {
            return Result::fail(self::errMess);
        }

        return Result::success($file);
    }

    /**
     * @OA\Post(
     *     path="/api/tik-files",
     *     summary="Thêm file",
     *     description="Thêm một file mới",
     *     tags={"Tik"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="File cần upload"
     *                 ),
     *                 @OA\Property(
     *                     property="folder",
     *                     type="string",
     *                     description="Thư mục lưu trữ file (tùy chọn)",
     *                     example="images"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="file_id", type="string", example="file_123456"),
     *                 @OA\Property(property="file_name", type="string", example="example.jpg"),
     *                 @OA\Property(property="file_type", type="string", example="image/jpeg"),
     *                 @OA\Property(property="file_url", type="string", example="https://example.com/storage/images/example.jpg"),
     *                 @OA\Property(property="file_size", type="integer", example=1024576)
     *             ),
     *             @OA\Property(property="message", type="string", example="File uploaded successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The file field is required"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="file",
     *                     type="array",
     *                     @OA\Items(type="string", example="The file field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error uploading file")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_id' => 'nullable|string',
            'file_name' => 'nullable|string',
            'file_type' => 'nullable|string',
            'file_url' => 'nullable|string|url',
        ]);

        if ($validator->fails()) {
            return Result::fail(self::errValidate);
        }
        try {
            $file = $this->tikFileRepository->create($request->all());
            if (!$file) {
                return Result::fail(self::errCreate);
            }
            return Result::success($file);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'nullable|file',
            'file_id' => 'nullable|string',
            'file_name' => 'nullable|string',
            'file_type' => 'nullable|string',
            'file_url' => 'nullable|string|url',
        ]);

        if ($validator->fails()) {
            return Result::fail(self::errValidate);
        }
        try {
            $file = $this->tikFileRepository->update($id, $request->all());

            if (!$file) {
                return Result::fail(self::errMess);
            }

            return Result::success($file);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tik-files/{id}",
     *     summary="Xóa file",
     *     description="Xóa một file cụ thể",
     *     operationId="tikFilesDestroy",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của file cần xóa",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/TikFile"),
     *             @OA\Property(property="message", type="string", example="File deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="File not found")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $file = $this->tikFileRepository->delete($id);

        if (!$file) {
            return Result::fail(self::errDelete);
        }

        return Result::success($file);
    }
}
