<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Repositories\ProductionNorm\ProductionNormInterface;

class ProductionNormController extends Controller
{
    protected $productionRepository;

    const errMess = 'Định mức sản xuất không tồn tại';
    const errUpdate = 'Cập nhật thất bại';
    const errDelete = 'Xóa thất bại';
    const errCreate = 'Thêm định mức sản xuất thất bại';

    public function __construct(ProductionNormInterface $productionRepository)
    {
        $this->productionRepository = $productionRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/production-norms",
     *     operationId="getAllProductionNorms",
     *     tags={"Inventory"},
     *     summary="Lấy danh sách định mức sản xuất",
     *     description="Lấy danh sách định mức sản xuất với các tùy chọn lọc qua query string.",
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         description="ID sản phẩm để lọc định mức sản xuất",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="material_id",
     *         in="query",
     *         description="ID nguyên vật liệu để lọc định mức sản xuất",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=2
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="season",
     *         in="query",
     *         description="Mùa sản xuất để lọc định mức sản xuất",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="Mùa hè"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách định mức sản xuất",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ProductionNormModel"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $production = $this->productionRepository->getAll($queryData);
        return Result::success($production);
    }

    /**
     * @OA\Get(
     *     path="/api/production-norms/{id}",
     *     operationId="getProductionNorm",
     *     tags={"Inventory"},
     *     summary="Lấy thông tin định mức sản xuất",
     *     description="Lấy thông tin chi tiết của định mức sản xuất theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của định mức sản xuất cần lấy thông tin",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trả về thông tin định mức sản xuất",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProductionNormModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy định mức sản xuất với ID cung cấp",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy định mức sản xuất")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        try {
            $production = $this->productionRepository->findById($id);
            if (!$production) {
                return Result::fail(self::errMess);
            }
            return Result::success($production);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errMess);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/production-norms",
     *     operationId="storeProductionNorm",
     *     tags={"Inventory"},
     *     summary="Tạo định mức sản xuất",
     *     description="Tạo mới một định mức sản xuất và trả về thông tin định mức sản xuất đã tạo.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Thông tin định mức sản xuất cần tạo",
     *         @OA\JsonContent(
     *             required={"product_id", "material_id", "norm_quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1, description="ID sản phẩm"),
     *             @OA\Property(property="material_id", type="integer", example=2, description="ID nguyên vật liệu"),
     *             @OA\Property(property="norm_quantity", type="number", format="float", example=10.5, description="Số lượng nguyên vật liệu cần để sản xuất 1 sản phẩm"),
     *             @OA\Property(property="season", type="string", example="Mùa hè", description="Mùa sản xuất (nếu có)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo định mức sản xuất thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProductionNormModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:inv_products,id',
            'material_id' => 'required|exists:inv_materials,id',
            'norm_quantity' => 'required|numeric',
            'season' => 'nullable|string|max:50',
        ]);
        try {
            $production = $this->productionRepository->create($data);
            if (!$production) {
                return Result::fail(self::errCreate);
            }
            return Result::success($production);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errCreate);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/production-norms/{id}",
     *     operationId="updateProductionNorm",
     *     tags={"Inventory"},
     *     summary="Cập nhật định mức sản xuất",
     *     description="Cập nhật thông tin của định mức sản xuất theo ID và trả về thông tin đã cập nhật.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của định mức sản xuất cần cập nhật",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Thông tin cần cập nhật cho định mức sản xuất",
     *         @OA\JsonContent(
     *             required={"product_id", "material_id", "norm_quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1, description="ID sản phẩm"),
     *             @OA\Property(property="material_id", type="integer", example=2, description="ID nguyên vật liệu"),
     *             @OA\Property(property="norm_quantity", type="number", format="float", example=10.5, description="Số lượng nguyên vật liệu cần để sản xuất 1 sản phẩm"),
     *             @OA\Property(property="season", type="string", example="Mùa hè", description="Mùa sản xuất (nếu có)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật định mức sản xuất thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProductionNormModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy định mức sản xuất với ID cung cấp",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy định mức sản xuất")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:inv_products,id',
            'material_id' => 'required|exists:inv_materials,id',
            'norm_quantity' => 'required|numeric',
            'season' => 'nullable|string|max:50',
        ]);
        try {
            $production = $this->productionRepository->update($id, $data);
            if (!$production) {
                return Result::fail(self::errUpdate);
            }
            return Result::success($production);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errUpdate);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/production-norms/{id}",
     *     operationId="deleteProductionNorm",
     *     tags={"Inventory"},
     *     summary="Xóa định mức sản xuất",
     *     description="Xóa định mức sản xuất theo ID và trả về kết quả thành công hoặc thất bại.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của định mức sản xuất cần xóa",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa định mức sản xuất thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Định mức sản xuất đã được xóa thành công")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy định mức sản xuất với ID cung cấp",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy định mức sản xuất")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $production = $this->productionRepository->delete($id);
            return Result::success($production);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errMess);
        }
    }
}
