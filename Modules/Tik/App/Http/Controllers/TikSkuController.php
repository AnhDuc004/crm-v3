<?php

namespace Modules\Tik\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Tik\Repositories\TikSku\TikSkuInterface;

class TikSkuController extends Controller
{
    protected $tikSkuRepository;
    const errMess = 'Không tìm thấy SKU';
    const errCreate = 'Không thể tạo SKU';
    const errUpdate = 'Không thể cập nhật SKU';
    const errDelete = 'Không thể xóa SKU';
    public function __construct(TikSkuInterface $tikSkuRepository)
    {
        $this->tikSkuRepository = $tikSkuRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/tik-sku",
     *     summary="Danh sách SKU",
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="sku_id",
     *         in="query",
     *         required=false,
     *         description="Filter by SKU ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         required=false,
     *         description="Filter by Product ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="seller_sku",
     *         in="query",
     *         required=false,
     *         description="Filter by Seller SKU",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TikSku")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $tikSkus = $this->tikSkuRepository->getAll($queryData);
        return Result::success($tikSkus);
    }

    /**
     * @OA\Get(
     *     path="/api/tik-sku/{id}",
     *     tags={"Tik"},
     *     summary="Lấy thông tin chi tiết một SKU",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của SKU",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/TikSku")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy SKU"
     *     ),
     *    security={{"bearer": {}}}
     * )
     */
    public function show($id)
    {
        $tikSku = $this->tikSkuRepository->findById($id);
        if (!$tikSku) {
            return Result::fail(self::errMess);
        }
        return Result::success($tikSku);
    }

    /**
     * @OA\Post(
     *     path="/api/tik-sku",
     *     tags={"Tik"},
     *     summary="Tạo mới SKU",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="sku_id", type="integer", example=123456),
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="seller_sku", type="string", example="SELLER-123"),
     *             @OA\Property(property="price", type="object", example={"original": 100, "discounted": 80}),
     *             @OA\Property(property="stock_infos", type="object", example={"quantity": 50, "status": "in_stock"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/TikSku")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi validate dữ liệu"
     *     ),
     *    security={{"bearer": {}}}
     * )
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'sku_id' => 'nullable|integer',
                'product_id' => 'nullable|integer|exists:tik_products,id',
            ]);
            $tikSku = $this->tikSkuRepository->create($request->all());
            if (!$tikSku) {
                return Result::fail(self::errCreate);
            }
            return Result::success($tikSku);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tik-sku/{id}",
     *     tags={"Tik"},
     *     summary="Cập nhật thông tin SKU",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của SKU",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="sku_id", type="integer", example=123456),
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="seller_sku", type="string", example="SELLER-123"),
     *             @OA\Property(property="price", type="object", example={"original": 100, "discounted": 80}),
     *             @OA\Property(property="stock_infos", type="object", example={"quantity": 50, "status": "in_stock"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/TikSku")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy SKU"
     *     ),
     *    security={{"bearer": {}}}
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'sku_id' => 'nullable|integer',
                'product_id' => 'nullable|integer|exists:tik_products,id',
            ]);
            $tikSku = $this->tikSkuRepository->update($id, $request->all());
            if (!$tikSku) {
                return Result::fail(self::errUpdate);
            }
            return Result::success($tikSku);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tik-sku/{id}",
     *     tags={"Tik"},
     *     summary="Xóa SKU",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của SKU",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy SKU"
     *     ),
     *    security={{"bearer": {}}}
     * )
     */
    public function destroy($id)
    {
        $tikSku = $this->tikSkuRepository->delete($id);
        if (!$tikSku) {
            return Result::fail(self::errDelete);
        }
        return Result::success($tikSku);
    }
}
