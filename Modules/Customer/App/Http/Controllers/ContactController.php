<?php

namespace Modules\Customer\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Result;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Repositories\Contact\ContactInterface;

class ContactController extends Controller
{
    protected $contactRepository;
    const errorMess = 'Người liên hệ không tồn tại';
    const errorCreateMess = 'Tạo người liên hệ thất bại';
    const errorUpdateMess = 'Cập nhật người liên hệ thất bại';
    const successDeleteMess = 'Xoá người liên hệ thành công';
    const errorDeleteMess = 'Xóa người liên hệ thất bại';
    const errorChangeStatusMess = 'Thay đổi người liên hệ thất bại';
    const errCustomerMess = "Khách hàng không tồn tại";

    public function __construct(ContactInterface $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    public function index(Request $request)
    {
        $data = $this->contactRepository->listAll($request->all());
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/contact/customer/{id_customer}",
     *     tags={"Customer"},
     *     summary="Create a new contact",
     *     description="Create a new contact with the given details.",
     *     operationId="createContact",
     *     @OA\Parameter(
     *         name="id_customer",
     *         in="path",
     *         description="customer_id of the contact to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Payload to create a new contact",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/ContactModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/ContactModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contact created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ContactModel")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'bail|required|string|max:200',
            'last_name' => 'bail|required|string|max:500',
            'email' => 'bail|required|string|max:500',
        ], [
            'first_name.*' => 'Chưa nhập tên',
            'last_name.*' => 'Chưa nhập tên',
            'email.*' => 'Email không hợp lệ'
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $request->all();
            $contact = $this->contactRepository->create($id, $data);
            if (!$contact) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($contact);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/contact/customer/{customer_id}",
     *     tags={"Customer"},
     *     summary="Get a specific contact customer by ID",
     *     description="Retrieve details of a specific contact customer by its ID.",
     *     operationId="getContactCustomerById",
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         description="customer_id of the contact to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by last_name,first_name,email ",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
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
    public function getListByCustomer(Request $request, $id)
    {
        $data = $request->all();
        $contact = $this->contactRepository->getListByCustomer($id, $data);
        return Result::success($contact);
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'bail|required|string|max:200',
                'last_name' => 'bail|required|string|max:500',
                'email' => 'bail|required|string|max:500',
            ], [
                'first_name.*' => 'Chưa nhập tên',
                'last_name.*' => 'Chưa nhập tên',
                'email.*' => 'Email không h��p lệ'
            ]);
            if ($validator->fails()) {
                return Result::requestInvalid($validator->errors());
            }
            $data = $request->all();
            $contact = $this->contactRepository->update($id, $data);
            if (!$contact) {
                return Result::fail(self::errorUpdateMess);
            }
            return Result::success($contact);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        $data = $this->contactRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }

    /**
     * @OA\Put(
     *     path="/api/contact/{id}/toggle-active",
     *     tags={"Customer"},
     *     summary="Thay đổi trạng thái của liên hệ",
     *     description="Cập nhật trạng thái theo ID liên hệ",
     *     operationId="toggleActiveByContact",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của liên hệ muốn thay đổi trạng thái",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ContactModel"),
     *         @OA\XmlContent(ref="#/components/schemas/ContactModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid contact ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contact ID not found"
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
            $contact = $this->contactRepository->toggleActive($id);
            return Result::success($contact);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorChangeStatusMess);
        }
    }

    public function findId($id)
    {
        $data = $this->contactRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }
}
