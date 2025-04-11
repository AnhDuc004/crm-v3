<?php

namespace Modules\Customer\App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Customer\Repositories\Notes\NoteInterface;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Result;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Entities\Note;

class NoteController extends Controller
{

    protected $noteRepository;
    const errorMess = 'Ghi chú không tồn tại';
    const errorCreateMess = "Thêm mới ghi chú thất bại";
    const errorUpdateMess = "Cập nhật ghi chú thất bại";
    const errorDeleteMess = "Xóa ghi chú thất bại";
    const errCustomerMess = "Khách hàng không tồn tại";

    public function __construct(NoteInterface $noteRepository)
    {
        $this->noteRepository = $noteRepository;
    }

    public function index(Request $request)
    {
        $data = $this->noteRepository->listAll($request->all());
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/note/customer/{rel_id}",
     *     tags={"Customer"},
     *     summary="Lấy danh sách ghi chú của khách hàng theo ID",
     *     description="Lấy danh sách ghi chú của khách hàng từ cơ sở dữ liệu.",
     *     operationId="getListByCustomerNotes",
     *     @OA\Parameter(
     *         name="rel_id",
     *         in="path",
     *         description="ID của khách hàng cần lấy ghi chú",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Tìm kiếm theo mô tả ghi chú",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Số trang cần lấy",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Số lượng bản ghi mỗi trang",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy ghi chú thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy ghi chú thành công"),
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(ref="#/components/schemas/NoteModel")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Khách hàng không tồn tại hoặc không có ghi chú",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy ghi chú của khách hàng")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Dữ liệu yêu cầu không hợp lệ")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByCustomer($id, Request $request)
    {
        $data = $this->noteRepository->getListByCustomer($id, $request->all());
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/note/customer/{id}/",
     *     summary="Tạo ghi chú mới liên quan đến khách hàng",
     *     tags={"Customer"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của khách hàng",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/NoteModel")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/NoteModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi đầu vào không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid input data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function createByCustomer(Request $request, $id)
    {
        try {
            $data = $this->noteRepository->createByCustomer($id, $request->all());
            if (!$data) {
                return Result::fail(static::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    public function show($id)
    {
        $data = $this->noteRepository->findId($id);
        if (!$data) {
            return Result::fail(static::errorMess);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->noteRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(static::errorUpdateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        $data = $this->noteRepository->destroy($id);
        if (!$data) {
            return Result::fail(static::errorDeleteMess);
        }
        return Result::success($data);
    }

    public function getByEstimaste($id, Request $request)
    {
        return Result::success($this->noteRepository->getByEstimaste($id, $request->all()));
    }

    public function createByEstimaste($id, Request $request)
    {
        try {
            $data = $this->noteRepository->createByEstimaste($id, $request->all());
            if (!$data) {
                return Result::fail(static::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/note/lead/{rel_id}",
     *     tags={"Customer"},
     *     summary="Get a specific note lead by ID",
     *     description="Retrieve details of a specific note lead by its ID.",
     *     operationId="getNoteLeadById",
     *     @OA\Parameter(
     *         name="rel_id",
     *         in="path",
     *         description="rel_id of the note to retrieve",
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
     *         @OA\JsonContent(ref="#/components/schemas/NoteModel"),
     *         @OA\XmlContent(ref="#/components/schemas/NoteModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Note lead not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getByLead($id, Request $request)
    {
        $note = $this->noteRepository->getByLead($id, $request->all());
        return Result::success($note);
    }

    public function createByLead($id, Request $request)
    {
        try {
            $data = $this->noteRepository->createByLead($id, $request->all());
            if (!$data) {
                return Result::fail(static::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/note/contract/{id}",
     *     tags={"Customer"},
     *     summary="Lấy danh sách ghi chú theo ID hợp đồng",
     *     description="Truy xuất danh sách các ghi chú liên quan đến ID hợp đồng cụ thể.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Contract ID",
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
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of notes retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/NoteModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request parameters"
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
    public function getListByContract($id, Request $request)
    {
        $data = $this->noteRepository->getListByContract($id, $request->all());
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/note/contract/{contract_id}",
     *     tags={"Customer"},
     *     summary="Thêm note mới",
     *     description="Tạo ghi chú mới cho một hợp đồng cụ thể được xác định bằng ID của hợp đồng đó.",
     *     operationId="createNoteByContract",
     *     @OA\Parameter(
     *         name="contract_id",
     *         in="path",
     *         description="ID của hợp đồng để tạo ghi chú cho",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Mô tả ghi chú"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tạo ghi chú thành công",
     *         @OA\JsonContent(ref="#/components/schemas/NoteModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Không tìm thấy đầu vào hoặc hợp đồng không hợp lệ"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ nội bộ"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function createByContract($id, Request $request)
    {
        try {
            $result = $this->noteRepository->createByContract($id, $request->all());
            return Result::success($result);
        } catch (Exception $e) {
            return Result::fail(self::errorCreateMess);
        }
    }

    public function getListByTicket($id, Request $request)
    {
        return Result::success($this->noteRepository->getListByTicket($id, $request->all()));
    }

    public function createByTicket($id, Request $request)
    {
        try {
            $data = $this->noteRepository->createByTicket($id, $request->all());
            if (!$data) {
                return Result::fail(static::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    public function getByProposal($id, Request $request)
    {
        return Result::success($this->noteRepository->getByProposal($id, $request->all()));
    }

    public function createByProposal($id, Request $request)
    {
        try {
            $data = $this->noteRepository->createByProposal($id, $request->all());
            if (!$data) {
                return Result::fail(static::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    public function getByInvoice($id, Request $request)
    {
        return Result::success($this->noteRepository->getByInvoice($id, $request->all()));
    }

    public function createByInvoice($id, Request $request)
    {
        try {
            $data = $this->noteRepository->createByInvoice($id, $request->all());
            if (!$data) {
                return Result::fail(static::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/note/staff/{rel_id}",
     *     tags={"Customer"},
     *     summary="Get a specific note staff by ID",
     *     description="Retrieve details of a specific note staff by its ID.",
     *     operationId="getNoteStaffById",
     *     @OA\Parameter(
     *         name="rel_id",
     *         in="path",
     *         description="rel_id of the note to retrieve",
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
     *         @OA\JsonContent(ref="#/components/schemas/NoteModel"),
     *         @OA\XmlContent(ref="#/components/schemas/NoteModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Note staff not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getByStaff($id, Request $request)
    {
        $note = $this->noteRepository->getByStaff($id, $request->all());
        return Result::success($note);
    }

    public function createByStaff($id, Request $request)
    {
        try {
            $data = $this->noteRepository->createByStaff($id, $request->all());
            if (!$data) {
                return Result::fail(static::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }
}
