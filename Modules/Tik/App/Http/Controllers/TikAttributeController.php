<?php

namespace Modules\Tik\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tik\Repositories\TikAttribute\TikAttributeInterface;

class TikAttributeController extends Controller
{
    protected $tikAttributeRepository;

    const errMess = 'Không tìm thấy thuộc tính';
    const errCreate = 'Không tạo được thuộc tính';
    const errUpdate = 'Không cập nhật được thuộc tính';
    const errDelete = 'Không xóa được thuộc tính';
    public function __construct(TikAttributeInterface $tikAttributeRepository)
    {
        $this->tikAttributeRepository = $tikAttributeRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/tik-attribute",
     *     summary="Get all attributes",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Lọc theo tên thuộc tính",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách thuộc tính",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TikAttribute"))
     *     ),
     *     @OA\Response(response=400, description="Request không hợp lệ")
     * )
     */
    public function index(Request $request)
    {
        $data = $this->tikAttributeRepository->getAll($request->all());
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/tik-attribute/{id}",
     *     summary="Id thuộc tính",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID thuộc tính"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin thuộc tính",
     *         @OA\JsonContent(ref="#/components/schemas/TikAttribute")
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy thuộc tính")
     * )
     */
    public function show($id)
    {
        $data = $this->tikAttributeRepository->findById($id);
        if (!$data) {
            return Result::fail(self::errMess);
        }
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/tik-attribute",
     *     summary="Thêm mới thuộc tính",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "attribute_type", "value_data_format"},
     *             @OA\Property(property="name", type="string", example="Color"),
     *             @OA\Property(property="attribute_type", type="integer", example=1),
     *             @OA\Property(property="value_data_format", type="string", example="String"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thuộc tính được tạo thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TikAttribute")
     *     ),
     *     @OA\Response(response=400, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=500, description="Lỗi tạo thuộc tính")
     * 
     * )
     */

    public function store(Request $request)
    {
        try {
            $data = $this->tikAttributeRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::errCreate);
            }
            return Result::success($data);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tik-attribute/{id}",
     *     summary="Cập nhật thuộc tính",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID thuộc tính"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "attribute_type", "value_data_format"},
     *             @OA\Property(property="name", type="string", example="Color"),
     *             @OA\Property(property="attribute_type", type="integer", example=1),
     *             @OA\Property(property="value_data_format", type="string", example="String"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thuộc tính được cập nhật thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TikAttribute")
     *     ),
     *     @OA\Response(response=400, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=404, description="Không tìm thấy thuộc tính"),
     *     @OA\Response(response=500, description="Lỗi cập nhật thuộc tính")
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $this->tikAttributeRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errUpdate);
            }
            return Result::success($data);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tik-attribute/{id}",
     *     summary="Xóa thuộc tính",
     *     description="Xóa thuộc tính theo ID",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của thuộc tính cần xóa.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video successfully deleted.",
     *         @OA\JsonContent(ref="#/components/schemas/TikAttribute")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Video not found.",
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        $data = $this->tikAttributeRepository->delete($id);
        if (!$data) {
            return Result::fail(self::errDelete);
        }
        return Result::success($data);
    }
}
