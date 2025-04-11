<?php

namespace Modules\Shp\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Shp\Repositories\ShpAttributes\ShpAttributeInterface;

class ShpAttributeController extends Controller
{
    protected $shpAttributeRepository;

    const errMess = 'Không tìm thấy bản ghi';
    const errSystem = 'Lỗi hệ thống';
    public function __construct(ShpAttributeInterface $shpAttributeRepository)
    {
        $this->shpAttributeRepository = $shpAttributeRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/shp-attribute",
     *     summary="Get list of attributes",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="filter",
     *         in="query",
     *         required=false,
     *         description="Optional filters for attributes",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ShpAttribute")
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
        $data = $this->shpAttributeRepository->getAll($queryData);
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/shp-attribute",
     *     summary="Create a new attribute",
     *     tags={"Shp"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/ShpAttribute")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Attribute created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ShpAttribute")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|integer|exists:shp_products,id',
            'original_value_name' => 'required|string|max:255',
            'shp_id' => 'nullable|integer',
            'attribute_value_list' => 'nullable|array',
            'value_unit' => 'nullable|string|max:50',
        ]);
        try {
            $attribute = $this->shpAttributeRepository->create($validatedData);

            return Result::success($attribute);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/shp-attribute/{id}",
     *     summary="Get a specific attribute",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ShpAttribute")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Attribute not found"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        $attribute = $this->shpAttributeRepository->findById($id);

        if (!$attribute) {
            return Result::fail(self::errMess);
        }

        return Result::success($attribute);
    }

    /**
     * @OA\Put(
     *     path="/api/shp-attribute/{id}",
     *     summary="Update an existing attribute",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/ShpAttribute")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attribute updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ShpAttribute")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Attribute not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $attribute = $this->shpAttributeRepository->findById($id);

        if (!$attribute) {
            return Result::fail(self::errMess);
        }

        $validatedData = $request->validate([
            'product_id' => 'sometimes|required|integer|exists:shp_products,id',
            'original_value_name' => 'sometimes|required|string|max:255',
            'shp_id' => 'nullable|integer',
            'attribute_value_list' => 'nullable|array',
            'value_unit' => 'nullable|string|max:50',
        ]);
        try {
            $updatedAttribute = $this->shpAttributeRepository->update($attribute, $validatedData);

            return Result::success($updatedAttribute);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errSystem);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/shp-attribute/{id}",
     *     summary="Delete an attribute",
     *     tags={"Shp"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attribute deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Attribute not found"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        $attribute = $this->shpAttributeRepository->findById($id);

        if (!$attribute) {
            return Result::fail(self::errMess);
        }
        $data = $this->shpAttributeRepository->delete($attribute);
        return Result::success($data);
    }
}
