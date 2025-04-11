<?php

namespace Modules\Support\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Support\Repositories\Ticket\TicketInterface;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    protected $ticketRepository;
    const errorMess = 'Vé không tồn tại';
    const errorCreateMess = 'Tạo vé thất bại';
    const errorUpdateMess = 'Chỉnh sửa vé thất bại';
    const successDeleteMess = 'Xoá vé thành công';
    const errorDeleteMess = 'Xoá vé thất bại';
    const errCustomerMess = "Khách hàng không tồn tại";
    const errorContactMess = "Liên hệ không tồn tại";

    public function __construct(TicketInterface $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }


    public function index(Request $request)
    {
        $data = $this->ticketRepository->listAll($request->all());
        return Result::success($data);
    }

    public function createByCustomer(Request $request, $id)
    {
        try {
            $data = $this->ticketRepository->createByCustomer($id, $request->all());
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    public function getListByCustomer($id, Request $request)
    {
        $data = $this->ticketRepository->getListByCustomer($id, $request->all());
        return Result::success($data);
    }

    public function show($id)
    {
        $data =  $this->ticketRepository->findId($id);
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        // $validator = Validator::make($request->all(), [
        //     'name' => 'bail|required|string|max:191',

        // ], [
        //     'name.required'=>'Chưa nhập tên khách hàng',
        //     'name.max'=>'Tên khách không quá 191 ký tự',
        // ]);
        try {
            $data = $this->ticketRepository->findId($id);
            if (!$data) {
                return Result::fail(self::errCustomerMess);
            }
            $ticket = $this->ticketRepository->update($id, $request->all());

            return Result::success($ticket);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->ticketRepository->findId($id);
            if (!$data) {
                return Result::fail(self::errCustomerMess);
            }
            $ticket = $this->ticketRepository->destroy($id);

            return Result::success($ticket);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorDeleteMess);
        }
    }

    public function count()
    {
        $data = $this->ticketRepository->count();
        return Result::success($data);
    }

    public function create(Request $request)
    {
        try {
            $data = $this->ticketRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function filterByTicket(Request $request)
    {
        $data = $this->ticketRepository->filterByTicket($request->all());
        return Result::success($data);
    }

    public function getListByProject($id, Request $request)
    {
        $data = $this->ticketRepository->getListByProject($id, $request->all());
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/ticket/project/{id_project}",
     *     tags={"Support"},
     *     summary="Create a new ticket",
     *     description="Create a new ticket with the given details, including tags, custom field values, and file uploads.",
     *     operationId="createTicket",
     *     @OA\Parameter(
     *         name="id_project",
     *         in="path",
     *         description="ID of the task to associate with the ticket",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Payload to create a new ticket",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/TicketModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/TicketModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(ref="#/components/schemas/TicketModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ticket created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/TicketModel")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function createByProject($id, Request $request)
    {
        try {
            $ticket = $this->ticketRepository->createByProject($id, $request->all());
            return Result::success($ticket);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    public function countByProject($id)
    {
        $data = $this->ticketRepository->countByProject($id);
        return Result::success($data);
    }
}
