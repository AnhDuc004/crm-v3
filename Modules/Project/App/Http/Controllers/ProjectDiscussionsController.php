<?php

namespace Modules\Project\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Project\Repositories\ProjectDiscussions\ProjectDiscussionsInterface;

class ProjectDiscussionsController extends Controller
{
    const messageCodeError = 'Thảo luận dự án không tồn tại';
    const messageCreateError = 'Tạo thảo luận dự án thất bại';
    const messageUpdateError = 'Cập nhật thảo luận dự án thất bại';
    const messageDeleteError = 'Xóa thảo luận dự án thất bại';
    const messageDeleteSuccess = 'Xóa thảo luận dự án thành công';
    protected $projectDiscussionRepository;

    public function __construct(ProjectDiscussionsInterface $projectDiscussionRepository)
    {
        $this->projectDiscussionRepository = $projectDiscussionRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/project/discussions/{project_id}",
     *     summary="Lấy danh sách thảo luận của dự án",
     *     description="Trả về danh sách các thảo luận thuộc về một dự án cụ thể. Có thể phân trang và tìm kiếm theo từ khóa.",
     *     operationId="listByProject",
     *     tags={"Project"},
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         required=true,
     *         description="ID của dự án cần lấy danh sách thảo luận",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Số lượng bản ghi mỗi trang (phân trang)",
     *         @OA\Schema(
     *             type="integer",
     *             example=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Từ khóa tìm kiếm trong chủ đề thảo luận",
     *         @OA\Schema(
     *             type="string",
     *             example="Chủ đề"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách thảo luận",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectDiscussionsModel"),
     *         @OA\XmlContent(ref="#/components/schemas/ProjectDiscussionsModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Không tìm thấy thảo luận phù hợp.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy thảo luận phù hợp.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Lỗi khi lấy danh sách thảo luận.")
     *         )
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function listByProject($project_id, Request $request)
    {
        return Result::success($this->projectDiscussionRepository->listByProject($project_id, $request->all()));
    }

    /**
     * @OA\Post(
     *     path="/api/project/discussions/{project_id}",
     *     summary="Tạo thảo luận mới cho dự án",
     *     description="Tạo một thảo luận mới cho một dự án cụ thể. Yêu cầu dữ liệu đầu vào bao gồm các thông tin cần thiết như chủ đề, mô tả và các thông tin liên quan.",
     *     operationId="createProjectDiscussion",
     *     tags={"Project"},
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         required=true,
     *         description="ID của dự án mà thảo luận sẽ được tạo",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="subject", type="string", description="Chủ đề của thảo luận", example="Chủ đề thảo luận mới"),
     *             @OA\Property(property="description", type="string", description="Mô tả chi tiết của thảo luận", example="Đây là mô tả thảo luận."),
     *             @OA\Property(property="show_to_customer", type="tinyint", description="Chỉ định thảo luận có hiển thị cho khách hàng hay không", example=1),
     *             @OA\Property(property="staff_id", type="integer", description="ID của nhân viên tham gia thảo luận", example=1),
     *             @OA\Property(property="contact_id", type="integer", description="ID của liên hệ tham gia thảo luận", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tạo thảo luận cho dự án thành công",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectDiscussionsModel")
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Không tạo được thảo luận dự án",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="status", type="string", example="fail"),
     *              @OA\Property(property="message", type="string", example="Không tạo được thảo luận dự á")
     *          )
     *      ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ khi tạo thảo luận",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Lỗi khi tạo thảo luận cho dự án.")
     *         )
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function store(Request $request, $project_id)
    {
        try {
            $data = $this->projectDiscussionRepository->create($project_id, $request->all());
            if (!$data) {
                return Result::fail(self::messageCreateError);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageCreateError);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/project/discussions/{project_id}",
     *     summary="Cập nhật thông tin thảo luận dự án",
     *     description="Cập nhật một thảo luận dự án theo ID dự án. Yêu cầu dữ liệu đầu vào bao gồm thông tin cần cập nhật.",
     *     operationId="updateProjectDiscussion",
     *     tags={"Project"},
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         required=true,
     *         description="ID của dự án chứa thảo luận cần cập nhật",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="subject", type="string", description="Chủ đề của thảo luận", example="Chủ đề mới"),
     *             @OA\Property(property="description", type="string", description="Mô tả của thảo luận", example="Cập nhật mô tả thảo luận"),
     *             @OA\Property(property="show_to_customer", type="integer", description="Hiển thị cho khách hàng hay không", example=1),
     *             @OA\Property(property="staff_id", type="integer", description="ID của thành viên", example=5),
     *             @OA\Property(property="contact_id", type="integer", description="ID của liên hệ", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectDiscussionsModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy thảo luận dự án",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Thảo luận dự án không tồn tại")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Lỗi khi cập nhật thảo luận dự án")
     *         )
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $this->projectDiscussionRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::messageUpdateError);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageUpdateError);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/project/discussions/{project_id}",
     *     summary="Xóa cuộc thảo luận của dự án",
     *     description="Xóa một cuộc thảo luận dự án dựa trên ID được cung cấp.",
     *     operationId="deleteProjectDiscussion",
     *     tags={"Project"},
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         required=true,
     *         description="ID của cuộc thảo luận cần xóa",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thành công",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectDiscussionsModel"),
     *         @OA\XmlContent(ref="#/components/schemas/ProjectDiscussionsModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy cuộc thảo luận",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy cuộc thảo luận.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Xóa thất bại.")
     *         )
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function destroy($id)
    {
        $data = $this->projectDiscussionRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::messageDeleteError);
        }
        return Result::success($data);
    }
}
