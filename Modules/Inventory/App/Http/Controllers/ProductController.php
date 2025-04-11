<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Repositories\Product\ProductInterface;

class ProductController extends Controller
{
    protected $productRepository;

    const errMess = 'Sản phẩm không tồn tại';
    const errUpdate = 'Cập nhật thất bại';
    const errCreate = 'Thêm thất bại';

    public function __construct(ProductInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     operationId="getAllProducts",
     *     tags={"Inventory"},
     *     summary="Lấy danh sách sản phẩm",
     *     description="Lấy danh sách tất cả các sản phẩm và có thể lọc theo các tham số truy vấn (query parameters).",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=false,
     *         description="Tên sản phẩm để lọc",
     *         @OA\Schema(
     *             type="string",
     *             example="Áo thun"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="unit_id",
     *         in="query",
     *         required=false,
     *         description="ID của đơn vị tính để lọc",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Số lượng bản ghi muốn lấy (phân trang)",
     *         @OA\Schema(
     *             type="integer",
     *             example=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy danh sách sản phẩm thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ProductModel")),
     *             @OA\Property(property="pagination", type="object", 
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="last_page", type="integer", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu truy vấn không hợp lệ")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $product = $this->productRepository->getAll($queryData);
        return Result::success($product);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     operationId="getProductById",
     *     tags={"Inventory"},
     *     summary="Lấy chi tiết sản phẩm theo ID",
     *     description="Lấy chi tiết của một sản phẩm dựa trên ID và trả về thông tin chi tiết.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của sản phẩm cần lấy",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy sản phẩm thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProductModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy sản phẩm.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sản phẩm không tồn tại",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy sản phẩm với ID đã cho.")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        try {
            $product = $this->productRepository->findById($id);
            if (!$product) {
                return Result::fail(self::errMess);
            }
            return Result::success($product);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errMess);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     operationId="storeProduct",
     *     tags={"Inventory"},
     *     summary="Tạo mới sản phẩm",
     *     description="Tạo mới một sản phẩm và trả về thông tin sản phẩm đã tạo.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Thông tin sản phẩm cần tạo",
     *         @OA\JsonContent(
     *             required={"name", "unit_id"},
     *             @OA\Property(property="name", type="string", example="Sản phẩm A", description="Tên sản phẩm"),
     *             @OA\Property(property="description", type="string", example="Mô tả sản phẩm A", description="Mô tả sản phẩm"),
     *             @OA\Property(property="unit_id", type="integer", example=1, description="ID của đơn vị tính")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo sản phẩm thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProductModel")
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_id' => 'required|exists:inv_units,id',
        ]);

        try {
            $product = $this->productRepository->create($data);
            if (!$product) {
                return Result::fail(self::errCreate);
            }
            return Result::success($product);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errCreate);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     operationId="updateProduct",
     *     tags={"Inventory"},
     *     summary="Cập nhật thông tin sản phẩm",
     *     description="Cập nhật thông tin của sản phẩm theo ID và trả về thông tin sản phẩm đã cập nhật.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của sản phẩm cần cập nhật",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Thông tin sản phẩm cần cập nhật",
     *         @OA\JsonContent(
     *             required={"name", "unit_id"},
     *             @OA\Property(property="name", type="string", example="Sản phẩm A", description="Tên sản phẩm"),
     *             @OA\Property(property="description", type="string", example="Mô tả sản phẩm A", description="Mô tả sản phẩm"),
     *             @OA\Property(property="unit_id", type="integer", example=1, description="ID của đơn vị tính")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật sản phẩm thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProductModel")
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
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_id' => 'required|exists:inv_units,id',
        ]);

        $product = $this->productRepository->update($id, $data);
        if (!$product) {
            return Result::fail(self::errUpdate);
        }
        return Result::success($product);
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     operationId="deleteProduct",
     *     tags={"Inventory"},
     *     summary="Xóa sản phẩm theo ID",
     *     description="Xóa một sản phẩm theo ID và trả về kết quả.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của sản phẩm cần xóa",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa sản phẩm thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="string", example="Sản phẩm đã được xóa thành công.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Không thể xóa sản phẩm")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $product = $this->productRepository->delete($id);
            return Result::success($product);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errMess);
        }
    }

    public function listSelect()
    {
        $data = $this->productRepository->listSelect();
        return Result::success($data);
    }
}
