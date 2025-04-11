<?php

namespace Modules\Admin\App\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Repositories\Countries\CountryInterface;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Result;
use Illuminate\Support\Facades\Log;

class CountryController extends Controller
{
    protected $countryRepository;
    const errorMess = 'Quốc gia không tồn tại';
    const errorCreateMess = 'Tạo người quốc gia thất bại';
    const errorUpdateMess = 'Cập nhật người quốc gia thất bại';
    const successDeleteMess = 'Xoá người quốc gia thành công';
    const errorDeleteMess = 'Xóa người quốc gia thất bại';

    public function __construct(CountryInterface $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/country",
     *     tags={"Admin"},
     *     summary="Lấy danh sách quốc gia",
     *     description="Truy xuất danh sách quốc gia với các tùy chọn lọc và sắp xếp.",
     *     operationId="getCountries",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Tìm kiếm theo tên ngắn hoặc tên đầy đủ",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="order_name",
     *         in="query",
     *         description="Cột để sắp xếp (mặc định: id)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="order_type",
     *         in="query",
     *         description="Hướng sắp xếp (asc hoặc desc, mặc định: desc)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Số kết quả trên mỗi trang",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Phản hồi thành công",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Countries")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $data = $this->countryRepository->listAll($queryData);
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/country",
     *     tags={"Admin"},
     *     summary="Tạo quốc gia mới",
     *     description="Tạo mới bản ghi quốc gia với thông tin được cung cấp",
     *     operationId="storeCountry",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu quốc gia",
     *         @OA\JsonContent(ref="#/components/schemas/Countries")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thao tác thành công",
     *         @OA\JsonContent(ref="#/components/schemas/Countries")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Đầu vào không hợp lệ"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'iso2' => 'bail|required|string',
            'short_name' => 'bail|required|string|min:2',
            'long_name' => 'bail|required|string|max:191'
        ], [
            'iso2.*' => 'không hợp lệ',
            'short_name.*' => 'không hợp lệ',
            'long_name.*' => 'không hợp lệ'
        ]);

        if ($validator->fails()) {
            return Result::fail($validator->errors());
        }
        try {
            $data = $this->countryRepository->create($validator->validated());

            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/country/{id}",
     *     tags={"Admin"},
     *     summary="Lấy quốc gia theo ID",
     *     description="Trả về thông tin quốc gia cho ID được chỉ định",
     *     operationId="getCountryById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của quốc gia cần lấy",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thao tác thành công",
     *         @OA\JsonContent(ref="#/components/schemas/Countries")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy quốc gia"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        $data = $this->countryRepository->findId($id);
        if (!$data) {
            Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    /**
     * @OA\Put(
     *     path="/api/country/{id}",
     *     tags={"Admin"},
     *     summary="Cập nhật quốc gia hiện có",
     *     description="Cập nhật bản ghi quốc gia với ID được chỉ định",
     *     operationId="updateCountry",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của quốc gia cần cập nhật",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu quốc gia",
     *         @OA\JsonContent(ref="#/components/schemas/Countries")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thao tác thành công",
     *         @OA\JsonContent(ref="#/components/schemas/Countries")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Đầu vào không hợp lệ"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy quốc gia"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'iso2' => 'bail|required|string',
            'short_name' => 'bail|required|string|min:2',
            'long_name' => 'bail|required|string|max:191'
        ], [
            'iso2.*' => 'không hợp lệ',
            'short_name.*' => 'không hợp lệ',
            'long_name.*' => 'không hợp lệ'
        ]);

        if ($validator->fails()) {
            return Result::fail($validator->errors());
        }
        try {
            $data = $this->countryRepository->update($id, $validator->validated());

            if (!$data) {
                return Result::fail(self::errorUpdateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/country/{id}",
     *     tags={"Admin"},
     *     summary="Xóa một quốc gia",
     *     description="Xóa quốc gia với ID được chỉ định",
     *     operationId="deleteCountry",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của quốc gia cần xóa",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thao tác thành công"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy quốc gia"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $data = $this->countryRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::errorDeleteMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorDeleteMess);
        }
    }
}
