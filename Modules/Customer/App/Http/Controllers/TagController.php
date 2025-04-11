<?php

namespace Modules\Customer\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Customer\Repositories\Tags\TagInterface;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Result;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
    protected $tagRepository;
    const messageCodeError = 'Tag không tồn tại';
    const messageCreate = 'Tạo Tag thất bại';
    const messageUpdate = 'Cập nhật Tag thất bại';
    const messageDelete = 'Xóa Tag thất bại';
    const messageError = 'Xảy ra lỗi';

    public function __construct(TagInterface $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/tag",
     *     tags={"Customer"},
     *     summary="Lấy danh sách các Tag",
     *     description="Lấy tất cả các Tag trong hệ thống.",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Trang hiện tại để phân trang",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Số lượng kết quả mỗi trang",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách Tag thành công",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TagModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dữ liệu yêu cầu không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server nội bộ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đã xảy ra lỗi không mong muốn")
     *         )
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function index(Request $request)
    {
        try {
            $tag = $this->tagRepository->listAll($request->all());
            return Result::success($tag);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageError);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/tag",
     *     tags={"Customer"},
     *     summary="Tạo mới một Tag",
     *     description="Tạo mới một Tag trong hệ thống.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Thông tin Tag cần tạo",
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="Tên của tag", example="abc")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tag tạo thành công",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/TagModel"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Chưa nhập tag")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server nội bộ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Không thể tạo tag")
     *         )
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string',
        ], [
            'name.required' => 'Chưa nhập tag',
        ]);

        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $request->all();
            $tag = $this->tagRepository->create($data);
            if (!$tag) {
                return Result::fail(static::messageCreate);
            }
            return Result::success($tag);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageCreate);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/tag/{id}",
     *     tags={"Customer"},
     *     summary="Lấy thông tin một Tag",
     *     description="Lấy thông tin một Tag theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của tag cần lấy thông tin",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag được tìm thấy",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/TagModel"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag không tìm thấy",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tag không tìm thấy")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server nội bộ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Không thể lấy thông tin tag")
     *         )
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function show($id)
    {
        try {
            $tag = $this->tagRepository->findId($id);
            if (!$tag) {
                return Result::fail(static::messageCodeError);
            }
            return Result::success($tag);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageCodeError);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tag/{id}",
     *     tags={"Customer"},
     *     summary="Cập nhật Tag",
     *     description="Cập nhật thông tin của một Tag theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của tag cần cập nhật",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu cập nhật của tag",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/TagModel"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag cập nhật thành công",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/TagModel"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu đầu vào không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag không tìm thấy",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tag không tìm thấy")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server nội bộ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Không thể cập nhật tag")
     *         )
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string',
        ], [
            'name.required' => 'Chưa nhập tag',
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $request->all();
            $tag = $this->tagRepository->update($id, $data);
            if (!$tag) {
                return Result::fail(static::messageUpdate);
            }
            return Result::success($tag);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageUpdate);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tag/{id}",
     *     tags={"Customer"},
     *     summary="Xóa Tag",
     *     description="Xóa một Tag theo ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của tag cần xóa",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag xóa thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tag xóa thành công")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag không tìm thấy",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tag không tìm thấy")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server nội bộ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Không thể xóa tag")
     *         )
     *     ),
     *     security={{"bearer": {}}},
     * )
     */
    public function destroy($id)
    {
        try {
            $tag = $this->tagRepository->destroy($id);
            if (!$tag) {
                return Result::fail(static::messageDelete);
            }
            return Result::success($tag);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageDelete);
        }
    }
}
