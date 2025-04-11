<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Result;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Repositories\Unit\UnitInterface;
use PgSql\Lob;

class UnitController extends Controller
{
    protected $unitRepository;

    const successDelete = 'Xóa Đơn vị thành công';
    const errMess = 'Không tìm thấy Đơn vị';
    const errUpdate = 'Cập nhật đơn vị thất bại';

    public function __construct(UnitInterface $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/unit",
     *     summary="Nhận danh sách Đơn vị tính",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=0,
     *             description="Số lượng Đơn vị tính trả về"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             description="Tìm theo name"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of units",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Unit"
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(
     *                     property="total",
     *                     type="integer",
     *                     description="Total number of units"
     *                 ),
     *                 @OA\Property(
     *                     property="count",
     *                     type="integer",
     *                     description="Number of units returned"
     *                 ),
     *                 @OA\Property(
     *                     property="per_page",
     *                     type="integer",
     *                     description="Units per page"
     *                 ),
     *                 @OA\Property(
     *                     property="current_page",
     *                     type="integer",
     *                     description="Current page number"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Error message"
     *             )
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $queryData = $request->all();
        $data = $this->unitRepository->listAll($queryData);
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/unit",
     *     summary="Tạo mới Đơn vị tính",
     *     tags={"Inventory"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo mới Đơn vị tính thành công",
     *         @OA\JsonContent(ref="#/components/schemas/Unit")
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $unit = $this->unitRepository->create($request);
        return Result::success($unit);
    }

    /**
     * @OA\Get(
     *     path="/api/unit/{id}",
     *     summary="Tìm Đơn vị tính theo ID của nó",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             description="ID của Đơn vị tính"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chi tiết Đơn vị tính",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Unit"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy Đơn vị tính",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Error message"
     *             )
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        try {
            $unit = $this->unitRepository->findId($id);
            if (!$unit) {
                return Result::fail(self::errMess);
            }
            return Result::success($unit);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errMess);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/unit/{id}",
     *     summary="cập nhật Đơn vị tính",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unit updated",
     *         @OA\JsonContent(ref="#/components/schemas/Unit")
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update($id, Request $request)
    {
        try {
            $unit = $this->unitRepository->update($id, $request);
            return Result::success($unit);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errUpdate);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/unit/{id}",
     *     summary="Xóa Đơn vị tính",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Xóa Đơn vị tính"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $unit = $this->unitRepository->destroy($id);

            if (!$unit) {
                return Result::fail(self::errMess);
            }

            return Result::success($unit);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errMess);
        }
    }
}
