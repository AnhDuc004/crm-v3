<?php

namespace Modules\Tik\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Tik\Repositories\TikAttributeValue\TikAttributeValueInterface;

class TikAttributeValueController extends Controller
{
    protected $tikAttributeValueRepository;

    const errMess = 'Không tìm thấy thuộc tính';
    const errSystem = 'Lỗi hệ thống';
    const errDelete = 'Không thể xóa giá trị thuộc tính';
    public function __construct(TikAttributeValueInterface $tikAttributeValueRepository)
    {
        $this->tikAttributeValueRepository = $tikAttributeValueRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/tik-attribute-value",
     *     summary="Danh sách giá trị thuộc tính",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Lọc theo tên giá trị thuộc tính",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách thuộc tính",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TikAttributeValue"))
     *     ),
     *     @OA\Response(response=400, description="Request không hợp lệ")
     * )
     */
    public function index(Request $request)
    {
        $dataQuery = $request->all();
        $data = $this->tikAttributeValueRepository->getAll($dataQuery);
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/tik-attribute-value/{id}",
     *     security={{"bearer":{}}},
     *     summary="Lấy thông tin giá trị thuộc tính",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID giá trị thuộc tính"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin giá trị thuộc tính",
     *         @OA\JsonContent(ref="#/components/schemas/TikAttributeValue")
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy giá trị thuộc tính")
     * )
     */

    public function show($id)
    {
        $data = $this->tikAttributeValueRepository->findById($id);
        if (!$data) {
            return Result::fail(self::errMess);
        }
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/tik-attribute-value",
     *     security={{"bearer":{}}},
     *     summary="Thêm giá trị thuộc tính",
     *     tags={"Tik"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"attribute_id", "name"},
     *             @OA\Property(property="attribute_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Red"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Giá trị thuộc tính được tạo thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TikAttributeValue")
     *     ),
     *     @OA\Response(response=400, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=500, description="Lỗi tạo giá trị thuộc tính")
     * )
     */

    public function store(Request $request)
    {
        $request->validate([
            'attribute_id' => 'required|integer|exists:tik_attributes,id',
            'name' => 'required|string',
        ]);
        try {
            $data = $this->tikAttributeValueRepository->create($request->all());
            return Result::success($data);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tik-attribute-value/{id}",
     *     security={{"bearer":{}}},
     *     summary="Cập nhật giá trị thuộc tính",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID giá trị thuộc tính"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"attribute_id", "name"},
     *             @OA\Property(property="attribute_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Blue"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Giá trị thuộc tính được cập nhật thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TikAttributeValue")
     *     ),
     *     @OA\Response(response=400, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=404, description="Không tìm thấy giá trị thuộc tính"),
     *     @OA\Response(response=500, description="Lỗi cập nhật giá trị thuộc tính")
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $this->tikAttributeValueRepository->findById($id);
            if (!$data) {
                return Result::fail(self::errMess);
            }
            $tikAttributeValue = $this->tikAttributeValueRepository->update($id, $request->all());
            return Result::success($tikAttributeValue);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tik-attribute-value/{id}",
     *     summary="Xóa giá trị thuộc tính",
     *     description="Xóa giá trị thuộc tính theo ID.",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của giá trị thuộc tính cần xóa.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video successfully deleted.",
     *         @OA\JsonContent(ref="#/components/schemas/TikAttributeValue")
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
        $tikAttributeValue = $this->tikAttributeValueRepository->delete($id);
        if (!$tikAttributeValue) {
            return Result::fail(self::errDelete);
        }
        return Result::success($tikAttributeValue);
    }
}
