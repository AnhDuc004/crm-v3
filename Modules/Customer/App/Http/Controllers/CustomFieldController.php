<?php

namespace Modules\Customer\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Result;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Repositories\CustomField\CustomFieldInterface;

class CustomFieldController extends Controller
{
    protected $customFieldRepository;

    const errorMess = 'Mục tự tạo không tồn tại';
    const errorCreateMess = 'Tạo mới mục tự tạo thất bại';
    const errorUpdateMess = 'Chỉnh sửa mục tự tạo thất bại';
    const successDeleteMess = 'Xoá mục tự tạo thành công';
    const errorDeleteMess = 'Xoá mục tự tạo thất bại';
    const errorStatusMess = "Thay đổi trạng thái thất bại";

    public function __construct(CustomFieldInterface $customFieldRepository)
    {
        $this->customFieldRepository = $customFieldRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->customFieldRepository->listAll($request->all()));
    }

    /**
     * @OA\Post(
     *     path="/api/customField",
     *     summary="Create a new custom field",
     *     description="Store a newly created custom field in storage.",
     *     operationId="createCustomField",
     *     tags={"Customer"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="field_to", type="string", description="Lĩnh vực cho", example="customers"),
     *             @OA\Property(property="name", type="string", description="Tên lĩnh vực", example="Abc"),
     *             @OA\Property(property="slug", type="string", description="Nội dung", example="bhome-fe"),
     *             @OA\Property(property="required", type="integer", description="Bắt buộc", example="1"),
     *             @OA\Property(property="type", type="string", description="Kiểu lĩnh vực", example="input"),
     *             @OA\Property(property="options", type="string", description="Lựa chọn", example="1.a, 2.b, 3.c"),
     *             @OA\Property(property="display_inline", type="integer", description="Hiển thị không", example="1"),
     *             @OA\Property(property="field_order", type="integer", description="Vị trí ưu tiên", example="1"),
     *             @OA\Property(property="active", type="integer", description="Hoạt động", example="1"),
     *             @OA\Property(property="show_on_pdf", type="integer", description="Hiển thị pdf", example="1"),
     *             @OA\Property(property="show_on_ticket_form", type="integer", description="Hiển thị trên mẫu vé", example="1"),
     *             @OA\Property(property="only_admin", type="integer", description="Chỉ có admin", example="1"),
     *             @OA\Property(property="show_on_table", type="integer", description="Hiển thị bảng", example="1"),
     *             @OA\Property(property="show_on_client_portal", type="integer", description="Hiển thị trên cổng thông tin khách hàng", example="1"),
     *             @OA\Property(property="disalow_client_to_edit", type="integer", description="Không cho khách hàng chỉnh sửa", example="1"),
     *             @OA\Property(property="bs_column", type="integer", description="Chia các cột", example="10"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully created",
     *         @OA\JsonContent(ref="#/components/schemas/CustomFieldModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function store(Request $request)
    {
        try {
            return Result::success($this->customFieldRepository->create($request->all()));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    public function getByName($id)
    {
        $data = $this->customFieldRepository->getByName($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success();
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->customFieldRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorUpdateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorStatusMess);
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->customFieldRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::errorDeleteMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorStatusMess);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/customField/{id}/toggle-active",
     *     tags={"Customer"},
     *     summary="Thay đổi trạng thái hoạt động của Custom Field",
     *     description="Thay đổi trạng thái hoạt động của Custom Field với ID.",
     *     operationId="toggleActiveByCustomField",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của Custom Field",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CustomFieldModel"),
     *         @OA\XmlContent(ref="#/components/schemas/CustomFieldModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid custom field ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Custom field ID not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function toggleActive($id)
    {
        try {
            $customField = $this->customFieldRepository->toggleActive($id);
            return Result::success($customField);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorStatusMess);
        }
    }

    public function show($id)
    {
        $data = $this->customFieldRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }
}
