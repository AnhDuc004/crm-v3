<?php

namespace Modules\Admin\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Admin\Repositories\Department\DepartmentInterface;

class DepartmentController extends Controller
{
    protected $departmentRepository;
    const errorMess = 'Phòng ban không tồn tại';
    const errorCreateMess = "Thêm mới phòng ban thất bại";
    const errorUpdateMess = "Cập nhật phòng ban thất bại";
    const errorDeleteMess = "Xóa phòng ban thất bại";
    const successDeleteMess = 'Xoá phòng ban thành công';

    public function __construct(DepartmentInterface $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }


    /**
     * @OA\Get(
     *     path="/api/department",
     *     summary="Lấy danh sách tất cả phòng ban",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Số lượng phòng ban trên mỗi trang (nếu không truyền sẽ lấy tất cả)",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Tìm kiếm theo tên hoặc email của phòng ban",
     *         @OA\Schema(type="string", example="VIP")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách phòng ban",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Department")
     *         )
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function index(Request $request)
    {
        $data = $this->departmentRepository->listAll($request->all());
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/department",
     *     summary="Tạo mới phòng ban",
     *     tags={"Admin"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu tạo phòng ban",
     *         @OA\JsonContent(ref="#/components/schemas/Department")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Phòng ban được tạo thành công",
     *         @OA\JsonContent(ref="#/components/schemas/Department")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ"
     *     ),
     *    security={{"bearer": {}}}
     * )
     */
    public function store(Request $request)
    {
        try {
            $department = $this->departmentRepository->create($request->all());
            return Result::success($department);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/department/{id}",
     *     summary="Lấy thông tin phòng ban theo ID",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của phòng ban",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin phòng ban",
     *         @OA\JsonContent(ref="#/components/schemas/Department")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy phòng ban"
     *     ),
     *    security={{"bearer": {}}}
     * )
     */
    public function show($id)
    {
        $department = $this->departmentRepository->findId($id);
        return Result::success($department);
    }

    /**
     * @OA\Put(
     *     path="/api/department/{id}",
     *     summary="Cập nhật thông tin phòng ban",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của phòng ban cần cập nhật",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu cập nhật phòng ban",
     *         @OA\JsonContent(ref="#/components/schemas/Department")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(ref="#/components/schemas/Department")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy phòng ban"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ"
     *     ),
     *   security={{"bearer": {}}}
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $department = $this->departmentRepository->update($id, $request->all());
            return Result::success($department);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/department/{id}",
     *     summary="Xóa phòng ban",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của phòng ban cần xóa",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Xóa phòng ban thành công")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy phòng ban"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ khi xóa phòng ban"
     *     ),
     *   security={{"bearer": {}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $department = $this->departmentRepository->destroy($id);
            return Result::success($department);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorDeleteMess);
        }
    }
}
