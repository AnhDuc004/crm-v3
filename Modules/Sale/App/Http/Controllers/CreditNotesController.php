<?php

namespace Modules\Sale\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Result;
use Modules\Sale\Repositories\CreditNotes\CreditNotesInterface;
use Illuminate\Support\Facades\Log;


class CreditNotesController extends Controller
{
    private $creditNoteRepository;
    const messageCodeError = 'Ghi chú tín dụng không tồn tại';
    const messageCreateError = 'Tạo ghi chú tín dụng thất bại';
    const messageUpdateError = 'Sửa ghi chú tín dụng thất bại';
    const messageDeleteError = 'Xóa ghi chú tín dụng thất bại';
    const messageDeleteSucces = 'Xóa ghi chú tín dụng thành công';

    public function __construct(CreditNotesInterface $creditNoteRepository)
    {
        $this->creditNoteRepository = $creditNoteRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/creditNotes",
     *     tags={"Sale"},
     *     summary="Get all creditNotes",
     *     description="Retrieve a list of all creditNotes.",
     *     operationId="getAllCreditNotes",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by project.name,customer.company, total,date and reference_no",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="rel",
     *         in="query",
     *         description="Filter tasks by rel",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="relId",
     *         in="query",
     *         description="Filter tasks by relId",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
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
     *             @OA\Items(ref="#/components/schemas/CreditNoteModel")
     *         ),
     *         @OA\XmlContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CreditNoteModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid task value"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        try {
            $creditNote = $this->creditNoteRepository->listAll($request->all());
            return Result::success($creditNote);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageCodeError);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/creditNotes",
     *     summary="Tạo một ghi chú tín dụng mới",
     *     description="Tạo một ghi chú tín dụng mới",
     *     tags={"Sale"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreditNoteModel")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Credit note created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/CreditNoteModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid data provided.")
     *         )
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $data = $this->creditNoteRepository->create($request->all());
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/creditNotes/{id}",
     *     summary="Tim kiem ghi chú tín dụng theo ID",
     *     description="Tìm kiếm ghi chú tín dụng theo ID",
     *     tags={"Sale"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của ghi chú tín dụng",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Credit note retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/CreditNoteModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Credit note not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Credit note not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unable to retrieve the credit note.")
     *         )
     *     ),
     *   security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        $data = $this->creditNoteRepository->findId($id);
        return Result::success($data);
    }

    /**
     * @OA\Put(
     *     path="/api/creditNotes/{id}",
     *     summary="Cập nhật ghi chú tín dụng",
     *     description="Cập nhật ghi chú tín dụng",
     *     tags={"Sale"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id của ghi chú tín dụng",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreditNoteModel")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Credit note updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/CreditNoteModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid data provided.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Credit note not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Credit note not found.")
     *         )
     *     ),
     *   security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $this->creditNoteRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(static::messageUpdateError);
            }
            return Result::success($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageUpdateError);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/creditNotes/{id}",
     *     summary="Delete a credit note",
     *     description="This API endpoint allows you to delete a credit note by providing its ID.",
     *     tags={"Sale"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the credit note to delete",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Credit note deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Credit note deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Credit note not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Credit note not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unable to delete the credit note.")
     *         )
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $data = $this->creditNoteRepository->destroy($id);
            if (!$data) {
                return Result::fail(static::messageCodeError);
            }
            return Result::success($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageDeleteError);
        }
    }

    // public function getListByProject($id, Request $request)
    // {
    //     return $this->creditNoteRepository->getListByProject($id, $request->all());
    // }

    /**
     * @OA\Get(
     *     path="/api/creditNotes/customer/{id}",
     *     summary="Lấy danh sách ghi chú tín dụng của khách hàng",
     *     description="Trả về danh sách ghi chú tín dụng của khách hàng theo ID",
     *     tags={"Sale"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Mã khách hàng",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
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
     *         description="Danh sách ghi chú tín dụng",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 ref="#/components/schemas/CreditNoteModel"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy ghi chú tín dụng",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Không tìm thấy ghi chú tín dụng.")
     *         )
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function getListByCustomer($id, Request $request)
    {
        $data = $this->creditNoteRepository->getListByCustomer($id, $request->all());
        return Result::success($data);
    }

    public function filterByCreditNote(Request $request)
    {
        $data = $this->creditNoteRepository->filterByCreditNote($request->all());
        return Result::success($data);
    }

    public function createByCustomer($id, Request $request)
    {
        $data = $this->creditNoteRepository->createByCustomer($id, $request->all());
        return Result::success($data);
    }
}
