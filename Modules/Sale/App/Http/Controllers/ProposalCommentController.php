<?php

namespace Modules\Sale\App\Http\Controllers;

use App\Helpers\Result;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Sale\Repositories\ProposalComment\ProposalCommentInterface;
use Illuminate\Support\Facades\Validator;

class ProposalCommentController extends Controller
{
    protected $proposalcommentRepository;

    const errorMess = 'Bình luận không tồn tại';
    const errorCreateMess = "Thêm mới bình luận thất bại";
    const errorUpdateMess = "Cập nhật bình luận thất bại";
    const errorDeleteMess = "Xóa bình luận thất bại";

    public function __construct(ProposalCommentInterface $proposalcommentRepository)
    {
        $this->proposalcommentRepository = $proposalcommentRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/proposalComment",
     *     tags={"Sale"},
     *     summary="Get all proposal comments",
     *     description="Fetch all proposal comments, with optional filtering and pagination.",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of comments per page for pagination",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search term to filter comments by content",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of all proposal comments",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ProposalComment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request due to invalid parameters",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid request parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function index(Request $request)
    {
        return Result::success($this->proposalcommentRepository->listAll($request));
    }

    /**
     * @OA\Post(
     *     path="/api/proposalComment/{id}",
     *     tags={"Sale"},
     *     summary="Tạo mói bình luận đề xuất",
     *     description="Create a comment for a specific proposal by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the proposal to add the comment to",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Comment data to be created",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ProposalComment"
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Proposal comment created successfully",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ProposalComment"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid input data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proposal not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proposal not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function store($id, Request $request)
    {
        try {
            $data = $this->proposalcommentRepository->create($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/proposalComment/{id}",
     *     tags={"Sale"},
     *     summary="Cập nhật bình luận đề xuất",
     *     description="Cập nhật bình luận cho đề xuất cụ thể theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của bình luận đề xuất cần cập nhật",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu cần cập nhật cho bình luận đề xuất",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ProposalComment"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bình luận đề xuất đã được cập nhật thành công",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ProposalComment"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu đầu vào không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dữ liệu đầu vào không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bình luận hoặc đề xuất không tìm thấy",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Không tìm thấy bình luận hoặc đề xuất")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server nội bộ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đã xảy ra lỗi không mong muốn")
     *         )
     *     ),
     *     security={{"bearer": {}}},
     * )
     */

    public function update(Request $request, $id)
    {
        try {
            $data = $this->proposalcommentRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/proposalComment/{id}",
     *     tags={"Sale"},
     *     summary="Xóa bình luận đề xuất",
     *     description="Xóa bình luận của một đề xuất theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của bình luận đề xuất cần xóa",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bình luận đề xuất đã được xóa thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bình luận đề xuất đã được xóa thành công")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bình luận hoặc đề xuất không tìm thấy",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Không tìm thấy bình luận hoặc đề xuất")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server nội bộ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đã xảy ra lỗi không mong muốn")
     *         )
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function destroy($id)
    {
        try {
            $data = $this->proposalcommentRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::errorDeleteMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorDeleteMess);
        }
    }
}
