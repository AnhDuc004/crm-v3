<?php

namespace Modules\Sale\App\Http\Controllers;

use App\Helpers\Result;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Sale\Repositories\Proposals\ProposalInterface;

class ProposalController extends Controller
{
    protected $proposalRepository;

    const errorMess = 'Đề xuất không tồn tại';
    const errorCreateMess = 'Thêm đề xuất thất bại';
    const errorUpdateMess = 'Sửa đề xuất thất bại';
    const errorDeleteMess = 'Xóa đề xuất thất bại';
    const succesDeleteMess = 'Xoá đề xuất thành công';
    const errorChangeActive = 'Thay đổi trạng thái thất bại';
    const errorCommentMess = 'Tạo bình luận thất bại';
    const errorUpdateCmtMess = 'Sửa bình luận thất bại';
    const errorDeleteCmtMess = 'Xóa bình luận thất bại';
    const errorCopyMess = 'Sao chép thất bại';

    public function __construct(ProposalInterface $proposalRepository)
    {
        $this->proposalRepository = $proposalRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/proposal",
     *     operationId="getProposals",
     *     tags={"Sale"},
     *     summary="List all proposals",
     *     description="Returns a list of proposals based on filter criteria.",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of results to return per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for proposal subject, total, date, or open till",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="subject",
     *         in="query",
     *         description="Filter by proposal subject",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="total",
     *         in="query",
     *         description="Filter by total amount",
     *         required=false,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter by date",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="open_till",
     *         in="query",
     *         description="Filter by open till date",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         description="Filter by tags",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="to",
     *         in="query",
     *         description="Filter by recipient",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Proposal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *    security={{"bearer": {}}},
     * )
     */
    public function index(Request $request)
    {
        $Proposals = $this->proposalRepository->listAll($request);
        return Result::success($Proposals);
    }

    /**
     * @OA\Post(
     *     path="/api/proposal",
     *     summary="Tạo mới một đề xuất",
     *     description="Tạo mới một đề xuất với các thông tin chi tiết",
     *     tags={"Sale"},
     *     @OA\RequestBody(
     *         required=true,
     *               @OA\JsonContent(ref="#/components/schemas/Proposal")        
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo mới báo giá thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/Proposal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Tạo mới báo giá thất bại",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Bad Request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ nội bộ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Internal Server Error")
     *         )
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function store(Request $request)
    {
        try {
            $data = $this->proposalRepository->create($request->all());
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/proposal/{id}",
     *     tags={"Sale"},
     *     summary="Lấy thông tin đề xuất",
     *     description="Lấy thông tin chi tiết của một đề xuất theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của đề xuất muốn lấy thông tin",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proposal found",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="rel_type", type="string", example="lead"),
     *             @OA\Property(property="rel_id", type="integer", example=123),
     *             @OA\Property(property="proposal_name", type="string", example="Proposal 1"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proposal not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proposal not found")
     *         )
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function show($id)
    {
        try {
            $data = $this->proposalRepository->findId($id);
            if (!$data) {
                return Result::fail(self::errorMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorMess);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/proposal/{id}",
     *     tags={"Sale"},
     *     summary="Cập nhật đề xuất",
     *     description="Cập nhật đề xuất theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của đề xuất muốn cập nhật",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Proposal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proposal updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Proposal")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bad Request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proposal not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proposal not found")
     *         )
     *     ),
     *       security={{"bearer": {}}},
     * )
     */
    public function update(Request $request, $id)
    {
        $data = $this->proposalRepository->update($id, $request->all());
        return Result::success($data);
    }

    /**
     * @OA\Delete(
     *     path="/api/proposal/{id}",
     *     tags={"Sale"},
     *     summary="Xóa đề xuất",
     *     description="Xóa đề xuất theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của đề xuất muốn xóa",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proposal deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proposal deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proposal not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proposal not found")
     *         )
     *     ),
     *       security={{"bearer": {}}},
     * )
     */
    public function destroy($id)
    {
        try {
            $data = $this->proposalRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::errorMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorDeleteMess);
        }
    }

    public function changeStatus($id, $status)
    {
        $data = $this->proposalRepository->changeStatus($id, $status);
        if (!$data) {
            return Result::fail(self::errorChangeActive);
        }
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/proposal/customer/{id}/",
     *     tags={"Sale"},
     *     summary="Get proposals by customer ID",
     *     description="Retrieve a list of proposals associated with a specific customer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the customer",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="filter",
     *         in="query",
     *         description="Filter parameters for proposals",
     *         required=false,
     *         @OA\Schema(type="string", example="status=active")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of proposals by customer ID",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Proposal")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid request parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Customer not found")
     *         )
     *     ),
     *     security={{"bearer":{}}},
     * )
     */
    public function getListByCustomer($id, Request $request)
    {
        $data = $this->proposalRepository->getListByCustomer($id, $request->all());
        return Result::success($data);
    }


    public function getListByLead($id, Request $request)
    {
        return Result::success($this->proposalRepository->getListByLead($id, $request->all()));
    }

    public function findItemable($id, Request $request)
    {
        $data = $this->proposalRepository->findItemable($id, $request->all());
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    public function createByLead($id, Request $request)
    {
        try {
            $data = $this->proposalRepository->createByLead($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function createByComment($id, Request $request)
    {
        try {
            $data = $this->proposalRepository->createByComment($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCommentMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCommentMess);
        }
    }

    public function getListByComment($id, Request $request)
    {
        return Result::success($this->proposalRepository->getListByComment($id, $request->all()));
    }

    public function updateByComment($id, Request $request)
    {
        try {
            $data = $this->proposalRepository->updateByComment($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorUpdateCmtMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateCmtMess);
        }
    }

    public function destroyByComment($id)
    {
        try {
            $data = $this->proposalRepository->destroyByComment($id);
            if (!$data) {
                return Result::fail(self::errorDeleteCmtMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorDeleteCmtMess);
        }
    }

    public function copyData($id)
    {
        try {
            $data = $this->proposalRepository->copyData($id);
            if (!$data) {
                return Result::fail(self::errorCopyMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCopyMess);
        }
    }

    public function filterByProposal(Request $request)
    {
        return Result::success($this->proposalRepository->filterByProposal($request->all()));
    }

    public function createByCustomer($id, Request $request)
    {
        try {
            $data = $this->proposalRepository->createByCustomer($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function count()
    {
        return Result::success($this->proposalRepository->count());
    }
}
