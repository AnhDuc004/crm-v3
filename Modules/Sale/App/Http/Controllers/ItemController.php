<?php

namespace Modules\Sale\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\Sale\Repositories\Item\ItemInterface;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    protected $itemRepository;
    const errorMess = 'Sản phẩm không tồn tại';
    const errorCreateMess = "Thêm mới sản phẩm thất bại";
    const errorUpdateMess = "Cập nhật sản phẩm thất bại";
    const errorDeleteMess = "Xóa sản phẩm thất bại";
    const successDeleteMess = "Xóa sản phẩm thành công";

    public function __construct(ItemInterface $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/item",
     *     tags={"Sale"},
     *     summary="Lấy tất cả Item",
     *     description="Lấy danh sách tất cả các Item",
     *     operationId="getAllItem",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Số lượng bản ghi trên mỗi trang. Nếu không được cung cấp hoặc đặt thành 0, tối đa 1000 bản ghi sẽ được trả về.",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ItemModel")
     *         ),
     *         @OA\XmlContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ItemModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid item value"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        try {
            $item = $this->itemRepository->listAll($request->all());
            return Result::success($item);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/item",
     *     summary="Tạo mới sản phẩm",
     *     description="Tạo mới một sản phẩm và lưu vào cơ sở dữ liệu.",
     *     operationId="storeItem",
     *     tags={"Sale"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ItemModel"  
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sản phẩm được tạo thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sản phẩm đã được tạo thành công"),
     *             @OA\Property(property="data", ref="#/components/schemas/ItemModel") 
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'rate' => 'required|numeric',
            'unit' => 'required|string|max:50',
            'group_id' => 'required|integer',
            'tax' => 'nullable|numeric',
            'tax2' => 'nullable|numeric',
            'long_description' => 'nullable|string',
        ]);

        $item = $this->itemRepository->create($validated);
        if (!$item) {
            return Result::fail(self::errorCreateMess);
        }
        return Result::success($item);
    }

    public function show($id)
    {
        $item = $this->itemRepository->findId($id);
        if (!$item) {
            return Result::fail(self::errorMess);
        }
        return Result::success($item);
    }

    public function update(Request $request, $id)
    {
        try {
            $item = $this->itemRepository->update($id, $request->all());
            if (!$item) {
                return Result::fail(self::errorUpdateMess);
            }
            return Result::success($item);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        $data = $this->itemRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
