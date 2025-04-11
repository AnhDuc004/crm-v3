<?php

namespace Modules\Tik\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Tik\Repositories\TikProductCertification\TikProductCertificationInterface;

class TikProductCertificationController extends Controller
{
    protected $tikProductCertificationRepository;

    const errMess = 'Không tìm thấy chứng nhận sản phẩm';
    const errCreate = 'Tạo chứng nhận sản phẩm thất bại';
    const errUpdate = 'Cập nhật chứng nhận sản phẩm thất bại';
    const errDelete = 'Xóa chứng nhận sản phẩm thất bại';

    public function __construct(TikProductCertificationInterface $tikProductCertificationRepository)
    {
        $this->tikProductCertificationRepository = $tikProductCertificationRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/tik-product-certifications",
     *     summary="Danh sách chứng nhận sản phẩm",
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
     *         description="Danh sách chứng nhận sản phẩm",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TikProductCertification"))
     *     ),
     *     @OA\Response(response=400, description="Request không hợp lệ")
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $certifications = $this->tikProductCertificationRepository->getAll($queryData);
        return Result::success($certifications);
    }

    /**
     * @OA\Get(
     *     path="/api/tik-product-certification/{id}",
     *     summary="Lấy thông tin chi tiết chứng nhận sản phẩm",
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
     *         description="Thông tin chứng nhận sản phẩm",
     *         @OA\JsonContent(ref="#/components/schemas/TikProductCertification")
     *     ),
     *     @OA\Response(response=404, description="Chứng nhận sản phẩm không tìm thấy")
     * )
     */
    public function show($id)
    {
        $certification = $this->tikProductCertificationRepository->findById($id);
        if (!$certification) {
            return Result::fail(self::errMess);
        }
        return Result::success($certification);
    }

    /**
     * @OA\Post(
     *     path="/api/tik-product-certification",
     *     summary="Tạo mới chứng nhận sản phẩm",
     *     security={{"bearer":{}}},
     *     tags={"Tik"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "files", "images", "title", "product_id"},
     *             @OA\Property(property="name", type="string", example="ISO 9001"),
     *             @OA\Property(property="files", type="array", items=@OA\Items(type="string", example="file_id_123")),
     *             @OA\Property(property="images", type="array", items=@OA\Items(type="string", example="image_url_123")),
     *             @OA\Property(property="title", type="string", example="Quality Certificate"),
     *             @OA\Property(property="product_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo chứng nhận sản phẩm thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TikProductCertification")
     *     ),
     *     @OA\Response(response=400, description="Request không hợp lệ")
     * )
     */
    public function store(Request $request)
    {
        $certification = $this->tikProductCertificationRepository->create(
            $request->all(),
            $request->file('files'),
            $request->file('images')
        );
        return Result::success($certification);
    }


    /**
     * @OA\Put(
     *     path="/api/tik-product-certification/{id}",
     *     summary="Cập nhật thông tin chứng nhận sản phẩm",
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
     *             required={"name", "files", "images", "title", "product_id"},
     *             @OA\Property(property="name", type="string", example="ISO 9001"),
     *             @OA\Property(property="files", type="array", items=@OA\Items(type="string", example="file_id_123")),
     *             @OA\Property(property="images", type="array", items=@OA\Items(type="string", example="image_url_123")),
     *             @OA\Property(property="title", type="string", example="Quality Certificate"),
     *             @OA\Property(property="product_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật chứng nhận sản phẩm thành công",
     *         @OA\JsonContent(ref="#/components/schemas/TikProductCertification")
     *     ),
     *     @OA\Response(response=404, description="Chứng nhận sản phẩm không tìm thấy")
     * )
     */
    public function update(Request $request, $id)
    {
        $certification = $this->tikProductCertificationRepository->update(
            $id,
            $request->all(),
        );
        return Result::success($certification);
    }

    /**
     * @OA\Delete(
     *     path="/api/tik-product-certification/{id}",
     *     summary="Xóa chứng nhận sản phẩm",
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
     *         description="Xóa chứng nhận sản phẩm thành công"
     *     ),
     *     @OA\Response(response=404, description="Chứng nhận sản phẩm không tìm thấy")
     * )
     */
    public function destroy($id)
    {
        $certification = $this->tikProductCertificationRepository->delete($id);
        if (!$certification) {
            return Result::fail(self::errDelete);
        }
        return Result::success($certification);
    }

    public function uploadFiles(Request $request, $id)
    {
        try {
            $files = $request->file('files');
            $images = $request->file('images');

            $certification = $this->tikProductCertificationRepository->uploadFiles($id, $files, $images);

            return  Result::success($certification);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }
}
