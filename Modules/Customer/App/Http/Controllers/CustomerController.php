<?php

namespace Modules\Customer\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Result;
use Exception;
use Modules\Customer\Repositories\Customer\CustomerInterface;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    private $customerRepository;
    const errorMess = 'Khách hàng không tồn tại';
    const errorCreateMess = "Thêm mới khách hàng thất bại";
    const errorUpdateMess = "Cập nhật khách hàng thất bại";
    const errorDeleteMess = "Xóa khách hàng thất bại";
    const errorStatusMess = "Thay đổi trạng thái thất bại";
    const errBulkAction = "Thay đổi hàng loạt thất bại";

    public function __construct(CustomerInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/customer/{id}",
     *     tags={"Customer"},
     *     summary="Get a specific customer by ID",
     *     description="Retrieve the details of a specific customer using its ID.",
     *     operationId="getCustomerById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the customer to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerModel"),
     *         @OA\XmlContent(ref="#/components/schemas/CustomerModel")
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
    public function show($id)
    {
        try {
            $customer = $this->customerRepository->findId($id);
            if (!$customer) {
                return Result::fail(static::errorMess);
            }
            return Result::success($customer);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/customer",
     *     tags={"Customer"},
     *     summary="Get all customers",
     *     description="Retrieve a list of all customers.",
     *     operationId="getAllCustomer",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by first_name, last_name, phone_number, name and email",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
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
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CustomerModel")
     *         ),
     *         @OA\XmlContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CustomerModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid customer value"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        try {
            $customer = $this->customerRepository->listAll($request->all());
            return Result::success($customer);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/customer",
     *     tags={"Customer"},
     *     summary="Create a new customer",
     *     description="Create a new customer with the given details.",
     *     operationId="createCustomer",
     *     @OA\RequestBody(
     *         description="Payload to create a new customer",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/CustomerModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/CustomerModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Customer created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerModel")
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
        $validator = Validator::make($request->all(), [
            'company' => 'bail|required|string|max:200',
            'address' => 'bail|nullable|string|max:500',
        ], [
            'company.required' => 'Chưa nhập tên công ty',
            'company.max' => 'Tên khách không quá 200 ký tự',
            'address.*' => 'Địa chỉ không quá 500 ký tự',

        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $request->all();
            $customer = $this->customerRepository->create($data);
            if (!$customer) {
                return Result::fail(static::errorCreateMess);
            }
            return Result::success($customer);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/customer/{id}",
     *     tags={"Customer"},
     *     summary="Update an existing customer",
     *     description="Update an existing customer by its ID.",
     *     operationId="updateCustomer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the customer to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Payload to update an existing customer",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/CustomerModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/CustomerModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation exception"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update($id, Request $request)
    {
        $validatorId = Validator::make(['id' => $id], [
            'id' => 'required|exists:customers,id'
        ], [
            'id.exists' => 'Khách hàng không tồn tại.'
        ]);

        if ($validatorId->fails()) {
            return Result::requestInvalid($validatorId->errors());
        }
        $validator = Validator::make($request->all(), [
            'company' => 'bail|required|string|max:200',
            'address' => 'bail|nullable|string|max:500',
        ], [
            'company.required' => 'Chưa nhập tên công ty',
            'company.max' => 'Tên khách không quá 200 ký tự',
            'address.*' => 'Địa chỉ không quá 500 ký tự',
        ]);

        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        Log::debug($request);
        try {
            $data = $request->all();
            $customer = $this->customerRepository->update($id, $data);
            if (!$customer) {
                return Result::fail(static::errorUpdateMess);
            }
            return Result::success($customer);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorUpdateMess);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/customer/{id}",
     *     tags={"Customer"},
     *     summary="Delete a customer",
     *     description="Delete a specific customer by its ID.",
     *     operationId="deleteCustomer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the customer to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid customer ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $customer = $this->customerRepository->destroy($id);
            if (!$customer) {
                return Result::fail(static::errorDeleteMess);
            }
            return Result::success($customer);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorDeleteMess);
        }
    }

    public function listSelect()
    {
        $data = $this->customerRepository->listSelect();
        return Result::success($data);
    }

    /**
     * @OA\Put(
     *     path="/api/customer/{id}/toggle-active",
     *     tags={"Customer"},
     *     summary="Thay đổi trạng thái của khách hàng",
     *     description="Cập nhật trạng thái theo ID khách hàng",
     *     operationId="toggleActiveByCustomer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của khách hàng muốn thay đổi trạng thái",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerModel"),
     *         @OA\XmlContent(ref="#/components/schemas/CustomerModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid customer ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer ID not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function toggleActive($id)
    {
        try {
            $customer = $this->customerRepository->toggleActive($id);
            return Result::success($customer);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorStatusMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/customer/count",
     *     tags={"Customer"},
     *     summary="Get the count of all customers",
     *     description="Retrieve the total count of all customers.",
     *     operationId="getSummaryCustomerCount",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="count",
     *                 type="integer",
     *                 description="Total count of customers"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid count value"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function count()
    {
        try {
            $customer = $this->customerRepository->count();
            return Result::success($customer);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/customer/statement/{customer_id}",
     *     tags={"Customer"},
     *     summary="Get customer statement",
     *     description="Retrieve the invoice for a specific customer.",
     *     operationId="getCustomerStatement",
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         description="The ID of the customer to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         description="startDate",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="date",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         description="endDate",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="date",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="statement",
     *         in="query",
     *         description="Statement 1-7",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerModel"),
     *         @OA\XmlContent(ref="#/components/schemas/CustomerModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invoice not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function statement($id, Request $request)
    {
        try {
            $customer = $this->customerRepository->statement($id, $request->all());
            if (!$customer) {
                return Result::fail(static::errorMess);
            }
            return Result::success($customer);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/customer/filter",
     *     tags={"Customer"},
     *     summary="Get all customer filters",
     *     description="Retrieve all customer filters based on different criteria.",
     *     operationId="getAllCustomerFilter",
     *     @OA\Parameter(
     *         name="group",
     *         in="query",
     *         description="Filter by customer group",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="invoice",
     *         in="query",
     *         description="Filter by invoice's customers",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="estimate",
     *         in="query",
     *         description="Filter by estimate",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="project",
     *         in="query",
     *         description="Filter by project customers",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="proposal",
     *         in="query",
     *         description="Filter by proposal customers",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="contractType",
     *         in="query",
     *         description="Filter by customers with contractType",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
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
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CustomerModel")
     *         ),
     *         @OA\XmlContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CustomerModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid filter by customer value"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function filterByCustomer(Request $request)
    {
        try {
            $customer = $this->customerRepository->filterByCustomer($request->all());
            return Result::success($customer);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/customer/inactive",
     *     tags={"Customer"},
     *     summary="Get all customers inactive",
     *     description="Retrieve a list of all customers inactive.",
     *     operationId="getAllCustomerInactive",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by first_name, last_name, phone_number, name and email",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
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
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CustomerModel")
     *         ),
     *         @OA\XmlContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CustomerModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid customer inactive value"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getInactiveCustomers(Request $request)
    {
        try {
            $customer = $this->customerRepository->getInactiveCustomers($request->all());
            return Result::success($customer);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:customers,id',
            'action' => 'required|string|in:delete,activate,deactivate,update_group',
            'group_id' => 'nullable|exists:customer_groups,id'
        ]);
        try {
            $data = $this->customerRepository->bulkAction($request);
            if (!$data) {
                return Result::fail(static::errBulkAction);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errBulkAction);
        }
    }
}
