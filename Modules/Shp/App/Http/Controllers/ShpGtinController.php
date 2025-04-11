<?php

namespace Modules\Shp\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Shp\Repositories\ShpGtin\ShpGtinInterface;

class ShpGtinController extends Controller
{
    protected $shpGtinRepository;

    const errMess = 'Không tìm thấy dữ liệu';

    public function __construct(ShpGtinInterface $shpGtinRepository)
    {
        $this->shpGtinRepository = $shpGtinRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/shp-gtin",
     *     summary="Get list of Gtin records",
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
     *         name="gtin_code",
     *         in="query",
     *         required=false,
     *         description="Filter by Gtin code",
     *         @OA\Schema(type="string")
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
     *             @OA\Items(ref="#/components/schemas/ShpGtin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index()
    {
        $queryData = request()->all();
        $data = $this->shpGtinRepository->getAll($queryData);
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/shp-gtin/{id}",
     *     summary="Get GTIN details by ID",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the GTIN record",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ShpGtin")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="GTIN record not found"
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
        $data = $this->shpGtinRepository->findById($id);
        if (!$data) {
            return Result::fail(self::errMess);
        }
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/shp-gtin",
     *     summary="Create a new GTIN record",
     *     tags={"Shp"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"shp_id", "product_id", "gtin_code"},
     *             @OA\Property(property="shp_id", type="integer", description="ID of the SHP order"),
     *             @OA\Property(property="product_id", type="integer", description="ID of the product, must exist in shp_products"),
     *             @OA\Property(property="gtin_code", type="string", maxLength=14, description="GTIN code of the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="GTIN record created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ShpGtin")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
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
            'gtin_code' => 'required|max:14',
        ]);
        try {
            $data = $this->shpGtinRepository->create($request->all());
            return Result::success($data);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/shp-gtin/{id}",
     *     summary="Update an existing GTIN record",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the GTIN record to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"shp_id", "product_id", "gtin_code"},
     *             @OA\Property(property="shp_id", type="integer", description="ID of the SHP order"),
     *             @OA\Property(property="product_id", type="integer", description="ID of the product, must exist in shp_products"),
     *             @OA\Property(property="gtin_code", type="string", maxLength=14, description="GTIN code of the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="GTIN record updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ShpGtin")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="GTIN record not found"
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
            'gtin_code' => 'required|max:14',
        ]);
        try {
            $data = $this->shpGtinRepository->update($id, $request->all());
            return Result::success($data);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/shp-gtin/{id}",
     *     summary="Delete a GTIN record",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the GTIN record to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="GTIN record deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="GTIN record not found"
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
        try {
            $data = $this->shpGtinRepository->delete($id);
            return Result::success($data);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }
}
