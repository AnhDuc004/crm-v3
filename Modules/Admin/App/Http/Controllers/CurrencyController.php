<?php

namespace Modules\Admin\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admin\Repositories\Currency\CurrencyInterface;
use Illuminate\Support\Facades\Log;
use App\Helpers\Result;
use Exception;

class CurrencyController extends Controller
{
    protected $currencyRepository;
    const errorMess = 'Tiền tệ không tồn tại';
    const errorCreateMess = 'Tạo người tiền tệ thất bại';
    const errorUpdateMess = 'Cập nhật người tiền tệ thất bại';
    const successDeleteMess = 'Xoá người tiền tệ thành công';
    const errorDeleteMess = 'Xóa người tiền tệ thất bại';

    public function __construct(CurrencyInterface $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/currencies",
     *     tags={"Admin"},
     *     summary="Liệt kê tất cả tiền tệ",
     *     description="Lấy danh sách tất cả tiền tệ với các tham số lọc tùy chọn.",
     *     operationId="listCurrencies",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Từ khóa tìm kiếm để lọc kết quả",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách tiền tệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CurrenciesModel"))
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $data = $this->currencyRepository->listAll($queryData);
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/currencies",
     *     tags={"Admin"},
     *     summary="Tạo tiền tệ mới",
     *     description="Tạo một tiền tệ mới với các thông tin cung cấp.",
     *     operationId="createCurrency",
     *     @OA\RequestBody(
     *         description="Dữ liệu để tạo tiền tệ mới",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/CurrenciesModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/CurrenciesModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo tiền tệ thành công",
     *         @OA\JsonContent(ref="#/components/schemas/CurrenciesModel")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi xác thực dữ liệu"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $currency = $this->currencyRepository->create($data);
            if (!$currency) {
                return Result::fail(static::errorCreateMess);
            }
            return Result::success($currency);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/currencies/{id}",
     *     tags={"Admin"},
     *     summary="Lấy thông tin tiền tệ theo ID",
     *     description="Lấy thông tin chi tiết của một tiền tệ cụ thể theo ID.",
     *     operationId="getCurrency",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của tiền tệ cần lấy thông tin",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin chi tiết tiền tệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="data", ref="#/components/schemas/CurrenciesModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy tiền tệ"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        $data =  $this->currencyRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    /**
     * @OA\Put(
     *     path="/api/currencies/{id}",
     *     tags={"Admin"},
     *     summary="Cập nhật tiền tệ",
     *     description="Cập nhật thông tin của một tiền tệ hiện có.",
     *     operationId="updateCurrency",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của tiền tệ cần cập nhật",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         description="Dữ liệu cập nhật tiền tệ",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/CurrenciesModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/CurrenciesModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật tiền tệ thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="data", ref="#/components/schemas/CurrenciesModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy tiền tệ"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi xác thực dữ liệu"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $this->currencyRepository->update($id, $request->all());
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
     *     path="/api/currencies/{id}",
     *     tags={"Admin"},
     *     summary="Xóa tiền tệ",
     *     description="Xóa một tiền tệ cụ thể theo ID.",
     *     operationId="deleteCurrency",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của tiền tệ cần xóa",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa tiền tệ thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy tiền tệ"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        $data = $this->currencyRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}