<?php

namespace Modules\Shp\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Shp\Repositories\ShpDimensions\ShpDimensionInterface;

class ShpDimensionController extends Controller
{
    protected $shpDimensionRepository;

    const errMess = 'Không tìm thấy bản ghi';
    const errCreate = 'Không thể tạo mới bản ghi';
    const errSystem = 'Lỗi hệ thống';
    public function __construct(ShpDimensionInterface $shpDimensionRepository)
    {
        $this->shpDimensionRepository = $shpDimensionRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/shp-dimension",
     *     summary="Get list of dimensions",
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
     *             @OA\Items(ref="#/components/schemas/ShpDimension")
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
        $data = $this->shpDimensionRepository->getAll($queryData);
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/shp-dimension",
     *     summary="Create a new dimension",
     *     tags={"Shp"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ShpDimension")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ShpDimension")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'shp_id' => 'required|integer',
            'product_id' => 'required|integer|exists:shp_products,id',
            'package_width' => 'required|numeric',
            'package_length' => 'required|numeric',
            'package_height' => 'required|numeric'
        ]);

        try {
            $dimension = $this->shpDimensionRepository->create($request->all());
            if (!$dimension) {
                return Result::fail(self::errCreate);
            }
            return Result::success($dimension);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/shp-dimension/{id}",
     *     summary="Get a dimension by ID",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the dimension",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ShpDimension")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Dimension not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        $dimension = $this->shpDimensionRepository->findById($id);
        if (!$dimension) {
            return Result::fail(self::errMess);
        }
        return Result::success($dimension);
    }

    /**
     * @OA\Put(
     *     path="/api/shp-dimension/{id}",
     *     summary="Update a dimension by ID",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the dimension",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ShpDimension")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ShpDimension")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Dimension not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'shp_id' => 'required|integer',
            'product_id' => 'required|integer|exists:shp_products,id',
            'package_width' => 'required|numeric',
            'package_length' => 'required|numeric',
            'package_height' => 'required|numeric'
        ]);

        try {
            $dimension = $this->shpDimensionRepository->update($id, $request->all());
            if (!$dimension) {
                return Result::fail(self::errMess);
            }
            return Result::success($dimension);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/shp-dimension/{id}",
     *     summary="Delete a dimension by ID",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the dimension",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ShpDimension")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Dimension not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        $dimension = $this->shpDimensionRepository->delete($id);
        if (!$dimension) {
            return Result::fail(self::errMess);
        }
        return Result::success($dimension);
    }
}
