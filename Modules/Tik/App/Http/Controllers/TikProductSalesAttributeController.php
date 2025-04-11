<?php

namespace Modules\Tik\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tik\Repositories\TikProductSalesAttribute\TikProductSalesAttributeInterface;

class TikProductSalesAttributeController extends Controller
{
    protected $tikProductSalesAttributeRepository;

    const errMess = 'Không tìm thấy thuộc tính bán hàng';
    const errCreate = 'Tạo thuộc tính bán hàng thất bại';
    const errUpdate = 'Cập nhật thuộc tính bán hàng thất bại';
    const errDelete = 'Xóa thuộc tính bán hàng thất bại';

    public function __construct(TikProductSalesAttributeInterface $tikProductSalesAttributeRepository)
    {
        $this->tikProductSalesAttributeRepository = $tikProductSalesAttributeRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/tik-product-sales-attribute",
     *     summary="Danh sách thuộc tính bán hàng",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         description="Lọc theo ID sản phẩm",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách thuộc tính bán hàng",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TikProductSalesAttribute"))
     *     ),
     *     @OA\Response(response=400, description="Request không hợp lệ")
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $salesAttributes = $this->tikProductSalesAttributeRepository->getAll($queryData);
        return Result::success($salesAttributes);
    }

    /**
     * @OA\Get(
     *     path="/api/tik-product-sales-attribute/{id}",
     *     summary="Lấy thông tin chi tiết thuộc tính bán hàng",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin thuộc tính bán hàng",
     *         @OA\JsonContent(ref="#/components/schemas/TikProductSalesAttribute")
     *     ),
     *     @OA\Response(response=404, description="Thuộc tính bán hàng không tìm thấy")
     * )
     */
    public function show($id)
    {
        $salesAttribute = $this->tikProductSalesAttributeRepository->findById($id);
        if (!$salesAttribute) {
            return Result::fail(self::errMess);
        }
        return Result::success($salesAttribute);
    }

    /**
     * @OA\Post(
     *     path="/api/tik-product-sales-attribute",
     *     summary="Tạo mới thuộc tính bán hàng",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"product_id", "attribute_id", "value_id"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="attribute_id", type="integer", example=1),
     *             @OA\Property(property="value_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo thuộc tính bán hàng thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TikProductSalesAttribute")
     *     ),
     *     @OA\Response(response=400, description="Request không hợp lệ")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|integer|exists:tik_products,id',
            'attribute_id' => 'nullable|integer|exists:tik_attributes,id',
            'value_id' => 'nullable|integer|exists:tik_attribute_values,id',
        ]);
        try {

            $data = $request->all();
            $salesAttribute = $this->tikProductSalesAttributeRepository->create($data);
            if (!$salesAttribute) {
                return Result::fail(self::errCreate);
            }
            return Result::success($salesAttribute);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tik-product-sales-attribute/{id}",
     *     summary="Cập nhật thông tin thuộc tính bán hàng",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"product_id", "attribute_id", "value_id"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="attribute_id", type="integer", example=1),
     *             @OA\Property(property="value_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thuộc tính bán hàng thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TikProductSalesAttribute")
     *     ),
     *     @OA\Response(response=404, description="Thuộc tính bán hàng không tìm thấy")
     * )
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'nullable|integer|exists:tik_products,id',
            'attribute_id' => 'nullable|integer|exists:tik_attributes,id',
            'value_id' => 'nullable|integer|exists:tik_attribute_values,id',
        ]);
        try {
            $data = $request->all();
            $salesAttribute = $this->tikProductSalesAttributeRepository->update($id, $data);
            if (!$salesAttribute) {
                return Result::fail(self::errUpdate);
            }
            return Result::success($salesAttribute);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tik-product-sales-attribute/{id}",
     *     summary="Xóa thuộc tính bán hàng",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thuộc tính bán hàng thành công"
     *     ),
     *     @OA\Response(response=404, description="Thuộc tính bán hàng không tìm thấy")
     * )
     */
    public function destroy($id)
    {
        $salesAttribute = $this->tikProductSalesAttributeRepository->delete($id);
        if (!$salesAttribute) {
            return Result::fail(self::errDelete);
        }
        return Result::success($salesAttribute);
    }
}
