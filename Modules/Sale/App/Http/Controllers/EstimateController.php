<?php

namespace Modules\Sale\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Sale\Repositories\Estimate\EstimateInterface;

class EstimateController extends Controller
{
    protected $estimateRepository;
    const errorMess = 'Báo giá không tồn tại';
    const errorCreateMess = "Thêm mới báo giá thất bại";
    const errorUpdateMess = "Cập nhật báo giá thất bại";
    const errorDeleteMess = "Xóa báo giá thất bại";
    const errCustomerMess = "Khách hàng không tồn tại";
    const errorChangeActive = 'Thay đổi trạng thái thất bại';
    const errorCopyMess = 'Sao chép thất bại';

    public function __construct(EstimateInterface $estimateRepository)
    {
        $this->estimateRepository = $estimateRepository;
    }

    public function index(Request $request)
    {
        $data = $this->estimateRepository->listAll($request->all());
        return Result::success($data);
    }

    public function getListByItemable($id, Request $request)
    {
        $data = $this->estimateRepository->getListByItemable($id, $request->all());
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/estimate",
     *     tags={"Sale"},
     *     summary="Tạo mới báo giá",
     *     description="Tạo một báo giá mới cùng với các mục liên quan, trường tùy chỉnh và thẻ.",
     *     security={{"bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *                 ref="#/components/schemas/EstimateModel",
     *             @OA\Property(
     *                 property="itemable",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="quantity", type="integer"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="qty", type="integer"),
     *                     @OA\Property(property="rate", type="number"),
     *                     @OA\Property(property="customFieldsValues", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="item_tax", type="array", @OA\Items(type="object"))
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="tag",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="tag_order", type="integer")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="customFieldsValues",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/EstimateModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'sale_agent' => 'required|integer',
        ]);
        try {
            $data = $this->estimateRepository->create($request->all());
            return Result::success($data);
        } catch (Exception $ex) {
            return Result::fail(self::errorCreateMess);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/estimate/customer/{customer_id}",
     *     tags={"Sale"},
     *     summary="Create a new estimate by customer",
     *     description="Create a new estimate for a specific customer along with its related items, custom fields, and tags.",
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Customer ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="customer_id", type="integer"),
     *             @OA\Property(
     *                 property="itemable",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="quantity", type="integer"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="qty", type="integer"),
     *                     @OA\Property(property="rate", type="number"),
     *                     @OA\Property(property="customFieldsValues", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="item_tax", type="array", @OA\Items(type="object"))
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="tag",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="tag_order", type="integer")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="customFieldsValues",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/EstimateModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function createByCustomer(Request $request, $id)
    {
        $request->validate([
            'sale_agent' => 'required|integer',
        ]);
        try {
            $data = $this->estimateRepository->createByCustomer($id, $request->all());
            return Result::success($data);
        } catch (Exception $ex) {
            return Result::fail(self::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/estimate/{id}",
     *     tags={"Sale"},
     *     summary="Tìm báo giá theo ID",
     *     description="Tìm báo giá theo ID cụ thể",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Estimate ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đã lấy báo giá thành công",
     *         @OA\JsonContent(ref="#/components/schemas/EstimateModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy báo giá"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ nội bộ"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function show($id)
    {
        $estimate = $this->estimateRepository->findId($id);
        if (!$estimate) {
            return Result::fail(self::errorMess);
        }
        return Result::success($estimate);
    }

    /**
     * @OA\Put(
     *     path="/api/estimate/{id}",
     *     tags={"Sale"},
     *     summary="Cập nhật báo giá",
     *     description="Cập nhật báo giá cùng với các mục liên quan, trường tùy chỉnh và thẻ.",
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Estimate ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             ref="#/components/schemas/EstimateModel",
     *             @OA\Property(
     *                 property="itemable",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="quantity", type="integer"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="qty", type="integer"),
     *                     @OA\Property(property="rate", type="number"),
     *                     @OA\Property(property="customFieldsValues", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="item_tax", type="array", @OA\Items(type="object"))
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="tag",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="tag_order", type="integer")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="customFieldsValues",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/EstimateModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'sale_agent' => 'required|integer',
        ]);
        try {
            $estimate = $this->estimateRepository->update($id, $request);
            if (!$estimate) {
                return Result::fail(self::errorMess);
            }
            return Result::success($estimate);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/estimate/{id}",
     *     summary="Xóa báo giá",
     *     description="Xóa báo giá theo ID.",
     *     operationId="deleteEstimate",
     *     tags={"Sale"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID báo giá",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted",
     *         @OA\JsonContent(ref="#/components/schemas/EstimateModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estimate not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $estimate = $this->estimateRepository->destroy($id);
            if (!$estimate) {
                return Result::fail(self::errorMess);
            }
            return Result::success($estimate);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorDeleteMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/estimate/customer/{customer_id}",
     *     tags={"Sale"},
     *     summary="Get estimate customer ",
     *     description="Retrieve the estimate  for a specific customer.",
     *     operationId="getEstimateCustomer",
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         description="The ID of the customer to retrieve the estimate  for",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by total, name, date and expiry_date",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="string",
     *             example=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of records per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/EstimateModel"),
     *         @OA\XmlContent(ref="#/components/schemas/EstimateModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByCustomer($id, Request $request)
    {
        try {
            $estimate = $this->estimateRepository->getListByCustomer($id, $request->all());
            if (!$estimate) {
                return Result::fail(static::errorMess);
            }
            return Result::success($estimate);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    public function countByCustomer($id)
    {
        $data = $this->estimateRepository->countByCustomer($id);
        return Result::success($data);
    }

    public function getListByProject($id, Request $request)
    {
        $data = $this->estimateRepository->getListByProject($id, $request->all());
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/estimate/project/year/{project_id}",
     *     tags={"Sale"},
     *     summary="Get estimate project year",
     *     description="Retrieve the estimate year for a specific project.",
     *     operationId="getEstimateProjectYear",
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         description="The ID of the project to retrieve the estimate year for",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Search by year",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="string",
     *             example="[2023,2024]"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/EstimateModel"),
     *         @OA\XmlContent(ref="#/components/schemas/EstimateModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByYearProject($id, Request $request)
    {
        try {
            $estimate = $this->estimateRepository->getListByYearProject($id, $request->all());
            if (!$estimate) {
                return Result::fail(static::errorMess);
            }
            return Result::success($estimate);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    public function getListByYearCustomer($id, Request $request)
    {
        return Result::success($this->estimateRepository->getListByYearCustomer($id, $request->all()));
    }

    /**
     * @OA\Get(
     *     path="/api/estimate/project/total/{project_id}",
     *     tags={"Sale"},
     *     summary="Get estimate project count",
     *     description="Retrieve the estimate Count for a specific project.",
     *     operationId="getEstimateProjectCount",
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         description="The ID of the project to retrieve the estimate count for",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of records per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/EstimateModel"),
     *         @OA\XmlContent(ref="#/components/schemas/EstimateModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function countEstimateByProject($id)
    {
        try {
            $estimate = $this->estimateRepository->countEstimateByProject($id);
            if (!$estimate) {
                return Result::fail(static::errorMess);
            }
            return Result::success($estimate);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    public function changeStatus($id, Request $request)
    {
        $data = $this->estimateRepository->changeStatus($id, $request->all());
        return Result::success($data);
    }

    public function copyData($id)
    {
        try {
            $data = $this->estimateRepository->copyData($id);
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    public function convertProposalToEstimaste($id, Request $request)
    {
        try {
            $data = $this->estimateRepository->convertProposalToEstimaste($id, $request->all());
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    public function countByStatus()
    {
        $data = $this->estimateRepository->countByStatus();
        return Result::success($data);
    }

    public function filterByEstimate(Request $request)
    {
        $data = $this->estimateRepository->filterByEstimate($request->all());
        return Result::success($data);
    }

    public function filterEstimateByProject($id, Request $request)
    {
        $data = $this->estimateRepository->filterEstimateByProject($id, $request->all());
        return Result::success($data);
    }

    public function getListByYear(Request $request)
    {
        $data = $this->estimateRepository->getListByYear($request->all());
        return Result::success($data);
    }
}
