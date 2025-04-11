<?php

namespace Modules\Customer\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\Customer\Entities\File;
use Modules\Customer\Repositories\File\FileInterface;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    protected $fileRepository;
    const errorMess = 'Tệp không tồn tại';
    const errorCreateMess = 'Thêm tệp thất bại';
    const errorUpdateMess = 'Sửa tệp thất bại';
    const errorDeleteMess = 'Xóa tệp thất bại';
    const errorChangeActive = 'Thay đổi trạng thái thất bại';

    public function __construct(FileInterface $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->fileRepository->listAll($request->all()));
    }

    public function getListByCustomer($id, Request $request)
    {
        return Result::success($this->fileRepository->getListByCustomer($id, $request->all()));
    }

    /**
     * @OA\Get(
     *     path="/api/file/lead/{rel_id}",
     *     tags={"Customer"},
     *     summary="Get a specific file by ID",
     *     description="Retrieve the details of a specific file using its ID.",
     *     operationId="getFileById",
     *     @OA\Parameter(
     *         name="rel_id",
     *         in="path",
     *         description="rel_id of the file to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
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
     *         @OA\JsonContent(ref="#/components/schemas/FileModel"),
     *         @OA\XmlContent(ref="#/components/schemas/FileModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByLead($id, Request $request)
    {
        $file = $this->fileRepository->getListByLead($id, $request->all());
        return Result::success($file);
    }

    /**
     * @OA\Get(
     *     path="/api/file/contract/{id}",
     *     tags={"Customer"},
     *     summary="Nhận danh sách các tập tin theo ID hợp đồng",
     *     description="Truy xuất danh sách các tệp liên quan đến ID hợp đồng cụ thể.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID hợp đồng",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Số lượng bản ghi trên một trang",
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
     *         description="Danh sách các tập tin đã được lấy thành công",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/FileModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Tham số yêu cầu không hợp lệ"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy hợp đồng"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ nội bộ"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function getListByContract($id, Request $request)
    {
        $data = $this->fileRepository->getListByContract($id, $request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $file = $this->fileRepository->create($request->all());
            if (!$file) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($file);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function show($id)
    {
        $data = $this->fileRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->fileRepository->update($request->all(), $id);
            if (!$data) {
                return Result::fail(self::errorUpdateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->fileRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::errorDeleteMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorDeleteMess);
        }
    }

    public function uploadFileByLead($id, Request $request)
    {
        try {
            $data = $this->fileRepository->uploadFileByLead($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function uploadFileByCustomer($id, Request $request)
    {
        try {
            $data = $this->fileRepository->uploadFileByCustomer($id, $request->all());
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
     * @OA\Post(
     *     path="/api/file/contract/{contract_id}",
     *     tags={"Customer"},
     *     summary="Tải lên một tệp cho hợp đồng",
     *     description="Tải lên một tệp cho hợp đồng cụ thể được xác định bởi ID của nó.",
     *     operationId="uploadFileByContract",
     *     @OA\Parameter(
     *         name="contract_id",
     *         in="path",
     *         description="ID của hợp đồng mà bạn muốn tải lên tệp",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"file_name", "added_by", "staff_id"},
     *                 @OA\Property(
     *                     property="file_name",
     *                     type="string",
     *                     format="binary",
     *                     description="Tệp cần tải lên"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     description="Mô tả về tệp"
     *                 ),
     *                 @OA\Property(
     *                     property="added_by",
     *                     type="integer",
     *                     description="ID của người tải lên tệp"
     *                 ),
     *                 @OA\Property(
     *                     property="staff_id",
     *                     type="integer",
     *                     description="ID của nhân viên"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tệp đã được tải lên thành công",
     *         @OA\JsonContent(ref="#/components/schemas/FileModel")
     *     ),
     *     @OA\Response(response=400, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=500, description="Lỗi máy chủ nội bộ"),
     *     security={{"bearer": {}}}
     * )
     */
    public function uploadFileByContract($id, Request $request)
    {
        try {
            $file = $this->fileRepository->uploadFileByContract($id, $request);
            if (!$file) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($file);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function changeVisibleToCustomer($id, Request $request)
    {
        try {
            $data = $this->fileRepository->changeVisibleToCustomer($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorChangeActive);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorChangeActive);
        }
    }

    public function uploadFileByProposal($id, Request $request)
    {
        try {
            $data = $this->fileRepository->uploadFileByProposal($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function getListByProposal($id, Request $request)
    {
        return Result::success($this->fileRepository->getListByProposal($id, $request->all()));
    }

    public function uploadFileByEstimate($id, Request $request)
    {
        try {
            $data = $this->fileRepository->uploadFileByEstimate($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function getListByEstimate($id, Request $request)
    {
        try {
            $data = $this->fileRepository->getListByEstimate($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function uploadFileByInvoice($id, Request $request)
    {
        try {
            $data = $this->fileRepository->uploadFileByInvoice($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function getListByInvoice($id, Request $request)
    {
        return Result::success($this->fileRepository->getListByInvoice($id, $request->all()));
    }

    /**
     * @OA\Post(
     *     path="/api/file/task/{id}",
     *     summary="Upload multiple files by task ID",
     *     tags={"Customer"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Task ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file_name"},
     *                 @OA\Property(
     *                     property="file_name",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Files to be uploaded"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Files uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="rel_id", type="integer", example=1),
     *             @OA\Property(property="rel_type", type="string", example="task"),
     *             @OA\Property(property="file_name", type="array", @OA\Items(type="string"), example={"image1.jpg", "image2.jpg"}),
     *             @OA\Property(property="file_type", type="array", @OA\Items(type="string"), example={"image/jpeg", "image/png"}),
     *             @OA\Property(property="staff_id", type="integer", example=1),
     *             @OA\Property(property="task_comment_id", type="integer", example=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid input data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized access")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function uploadFileByTask($id, Request $request)
    {
        try {
            $data = $this->fileRepository->uploadFileByTask($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function getListByTask($id, Request $request)
    {
        try {
            $data = $request->all();
            $file = $this->fileRepository->getListByTask($id, $data);
            if (!$file) {
                return Result::fail(static::errorMess);
            }
            return Result::success($file);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    public function download($id)
    {
        $file = File::find($id);
        $filePath = public_path("/uploads/file/" . $file->file_name);
        $headers = ['Content-Type'];
        $fileName = time();
        return response()->download($filePath, $fileName, $headers);
    }
}
