<?php

namespace Modules\Task\App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Task\Repositories\Reminders\ReminderInterface;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Result;
use Illuminate\Support\Facades\Log;

class ReminderController extends Controller
{
    protected $reminderRepo;
    const errorMess = 'Nhắc nhở không tồn tại';
    const errorCreateMess = "Thêm mới nhắc nhở thất bại";
    const errorUpdateMess = "Cập nhật nhắc nhở thất bại";
    const errorDeleteMess = "Xóa nhắc nhở thất bại";
    const errorPaymentMess = "Không thể thanh toán nhắc nhở";
    const successDeleteMess = "Xoá lời nhắc thành công";

    public function __construct(ReminderInterface $reminderRepo)
    {
        $this->reminderRepo = $reminderRepo;
    }

    /**
     * Xác thực ID khách hàng tồn tại trong bảng leads
     *
     * @param int $id
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateCustomerId($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'bail|required|exists:customers,id'
        ], [
            'id.exists' => 'Id khách hàng không hợp lệ',
        ]);

        return $validator;
    }

    /**
     * Xác thực ID khách hàng tồn tại trong bảng leads
     *
     * @param int $id
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateLeadId($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'bail|required|exists:leads,id'
        ], [
            'id.exists' => 'Id khách hàng không hợp lệ',
        ]);
        return $validator;
    }

    /**
     * Xác thực dữ liệu request của phương thức createByLead
     *
     * @param \Illuminate\Http\Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'bail|required|exists:staff,id',
            'description' => 'bail|nullable|string|max:500',
            'date' => 'bail|nullable|date',
            'notify_by_email' => 'bail|required|numeric'
        ], [
            'description.max' => 'Mô tả không quá 500 ký tự',
            'staff_id.exists' => 'Nhân viên không hợp lệ',
            'staff_id.required' => 'Chưa nhập nhân viên',
        ]);

        return $validator;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        return $this->reminderRepo->listAll($request);
    }

    /**
     * @OA\Post(
     *     path="/api/reminder/customer/{id_customer}",
     *     tags={"Task"},
     *     summary="Create a new reminder customer",
     *     description="Create a new reminder customer with the given details.",
     *     operationId="createReminderByCustomer",
     *     @OA\Parameter(
     *         name="id_customer",
     *         in="path",
     *         description="id_customer of the table reminder to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Payload to create a new reminder customer",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/ReminderModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/ReminderModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Reminder created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ReminderModel")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function createByCustomer($id, Request $request)
    {
        // Xác thực ID khách hàng
        $validator = $this->validateCustomerId($id);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }

        // Xác thực dữ liệu request
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }

        try {
            $reminder = $this->reminderRepo->createByCustomer($id, $request->all());
            if (!$reminder) {
                return Result::fail(static::errorCreateMess);
            }
            return Result::success($reminder);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/reminder/lead/{id_lead}",
     *     tags={"Task"},
     *     summary="Create a new reminder customer",
     *     description="Create a new reminder customer with the given details.",
     *     operationId="createReminderByLead",
     *     @OA\Parameter(
     *         name="id_lead",
     *         in="path",
     *         description="id_lead of the table reminder to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Payload to create a new reminder customer",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/ReminderModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/ReminderModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Reminder created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ReminderModel")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function createByLead($id, Request $request)
    {
        // Xác thực dữ liệu request
        $validator = $this->validateLeadId($id);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }

        // Xác thực dữ liệu request
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }

        try {
            $lead = $this->reminderRepo->createByLead($id, $request->all());
            return Result::success($lead);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return $this->reminderRepo->findId($id);
    }

    public function getListByCustomer($id, Request $request)
    {
        return $this->reminderRepo->getListByCustomer($id, $request);
    }

    public function getListByExpense($id, Request $request)
    {
        return $this->reminderRepo->getListByExpense($id, $request);
    }

    public function getListByLead($id, Request $request)
    {
        return $this->reminderRepo->getListByLead($id, $request);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'staff' => 'bail|required|exists:staff,id',
            'description' => 'bail|nullable|string|max:500',
            'date' => 'bail|nullable|date',
            'notify_by_email' => 'bail|required|numeric'
        ], [
            'description.max' => 'Mô tả không quá 500 ký tự',
            'date_contacted.date' => 'Ngày không hợp lệ',
            'staff.exists' => 'Nhân viên không hợp lệ',
            'staff.required' => 'Chưa nhập nhân viên',
            'dateadded.date' => 'Ngày không hợp lệ'
        ]);

        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }

        return $this->reminderRepo->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        return $this->reminderRepo->destroy($id);
    }

    public function getByEstimaste($id, Request $request)
    {
        return $this->reminderRepo->getByEstimaste($id, $request->all());
    }

    public function createByExpense($id, Request $request)
    {
        return $this->reminderRepo->createByExpense($id, $request->all());
    }

    public function getListByTicket($id, Request $request)
    {
        return $this->reminderRepo->getListByTicket($id, $request->all());
    }

    public function createByTicket($id, Request $request)
    {
        return $this->reminderRepo->createByTicket($id, $request->all());
    }

    public function getListByProposal($id, Request $request)
    {
        return $this->reminderRepo->getListByProposal($id, $request->all());
    }

    public function createByProposal($id, Request $request)
    {
        return $this->reminderRepo->createByProposal($id, $request->all());
    }

    public function createByEstimate($id, Request $request)
    {
        return $this->reminderRepo->createByEstimate($id, $request->all());
    }

    public function getListByInvoice($id, Request $request)
    {
        return $this->reminderRepo->getListByInvoice($id, $request->all());
    }

    public function createByInvoice($id, Request $request)
    {
        return $this->reminderRepo->createByInvoice($id, $request->all());
    }

    public function getListByTask($id, Request $request)
    {
        return $this->reminderRepo->getListByTask($id, $request->all());
    }

    /**
     * @OA\Post(
     *     path="/api/reminder/task/{id}",
     *     tags={"Task"},
     *     summary="Create a reminder for a task",
     *     description="Create a reminder for a specific task based on the provided ID.",
     *     operationId="createByTask",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Reminder object that needs to be added to the task",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ReminderModel")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reminder created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ReminderModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function createByTask($id, Request $request)
    {
        $data = $this->reminderRepo->createByTask($id, $request->all());
        return Result::success($data);
    }


    public function getListByCreditNote($id, Request $request)
    {
        return $this->reminderRepo->getListByCreditNote($id, $request->all());
    }

    public function createByCreditNote($id, Request $request)
    {
        return $this->reminderRepo->createByCreditNote($id, $request->all());
    }
}
