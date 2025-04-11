<?php

namespace Modules\Shp\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Shp\Repositories\ShpVideo\ShpVideoInterface;

class ShpVideoController extends Controller
{
    protected $shpVideoRepository;

    const errMess = 'Không tìm thấy video';
    const errUpdate = 'Cập nhật video không thành công';
    const errCreate = 'Tạo video không thành công';
    const errDelete = 'Xóa video không thành công';
    public function __construct(ShpVideoInterface $shpVideoRepository)
    {
        $this->shpVideoRepository = $shpVideoRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/shp-video",
     *     summary="Danh sách video",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="shp_id",
     *         in="query",
     *         required=false,
     *         description="Filter by SHP ID",
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
     *             @OA\Items(ref="#/components/schemas/ShpVideo")
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
        $shpVideos = $this->shpVideoRepository->getAll($queryData);
        return Result::success($shpVideos);
    }

    /**
     * @OA\Get(
     *     path="/api/shp-video/{id}",
     *     summary="Lấy thông tin video",
     *     description="Id của video cần lấy thông tin.",
     *     operationId="getShpVideoById",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the video to fetch.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video successfully fetched.",
     *         @OA\JsonContent(ref="#/components/schemas/ShpVideo")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Video not found.",
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        $shpVideo = $this->shpVideoRepository->findById($id);
        if (!$shpVideo) {
            return Result::fail(self::errMess);
        }
        return Result::success($shpVideo);
    }

    /**
     * @OA\Post(
     *     path="/api/shp-video",
     *     summary="Thêm video",
     *     description="Thêm video mới.",
     *     operationId="createShpVideo",
     *     tags={"Shp"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/ShpVideo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Video successfully created.",
     *         @OA\JsonContent(ref="#/components/schemas/ShpVideo")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error.",
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'shp_id' => 'nullable|integer',
            'product_id' => 'required|integer',
            'video_url' => 'nullable|string|max:255',
            'thumbnail_url' => 'nullable|string|max:255',
            'duration' => 'nullable|integer',
        ]);
        try {
            $shpVideo = $this->shpVideoRepository->create($request->all());
            if (!$shpVideo) {
                return Result::fail(self::errCreate);
            }
            return Result::success($shpVideo);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/shp-video/{id}",
     *     summary="Cập nhật video",
     *     description="Id video cần cập nhật.",
     *     operationId="updateShpVideo",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id của video cần cập nhật.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/ShpVideo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video successfully updated.",
     *         @OA\JsonContent(ref="#/components/schemas/ShpVideo")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error.",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Video not found.",
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'shp_id' => 'nullable|integer',
            'product_id' => 'required|integer',
            'video_url' => 'nullable|string|max:255',
            'thumbnail_url' => 'nullable|string|max:255',
            'duration' => 'nullable|integer',
        ]);
        try {
            $shpVideo = $this->shpVideoRepository->update($id, $request->all());
            if (!$shpVideo) {
                return Result::fail(self::errUpdate);
            }
            return Result::success($shpVideo);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/shp-video/{id}",
     *     summary="Xóa video",
     *     description="Xóa video theo ID.",
     *     operationId="deleteShpVideo",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của video cần xóa.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video successfully deleted.",
     *         @OA\JsonContent(ref="#/components/schemas/ShpVideo")
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
        $shpVideo = $this->shpVideoRepository->delete($id);
        if (!$shpVideo) {
            return Result::fail(self::errDelete);
        }
        return Result::success($shpVideo);
    }
}
