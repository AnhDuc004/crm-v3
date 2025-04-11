<?php

namespace Modules\Expense\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Expense\Repositories\Expenses\ExpensesInterface;
use Illuminate\Support\Facades\Log;
use App\Helpers\Result;
use Exception;

class ExpensesController extends Controller
{
    protected $expensesRepository;
    const errorMess = 'Chi phí không tồn tại';
    const errorCreateMess = 'Thêm chi phí thất bại';
    const errorUpdateMess = 'Sửa chi phí thất bại';
    const errorDeleteMess = 'Xóa chi phí thất bại';
    const errCustomerMess = "Khách hàng không tồn tại";

    public function __construct(ExpensesInterface $expensesRepository)
    {
        $this->expensesRepository = $expensesRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/expenses",
     *     tags={"Expense"},
     *     summary="Get all expenses",
     *     description="Retrieve a list of all expenses.",
     *     operationId="getAllExpenses",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by company, expense_name, date and name",
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
     *             @OA\Items(ref="#/components/schemas/ExpensesModel")
     *         ),
     *         @OA\XmlContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ExpensesModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid expenses value"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        try {
            $data = $request->all();
            $expenses = $this->expensesRepository->listAll($data);
            if (!$expenses) {
                return Result::fail(static::errorMess);
            }
            return Result::success($expenses);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/expenses/customer/{id}",
     *     summary="Lấy danh sách chi phí theo khách hàng",
     *     description="Lấy danh sách chi phí dựa trên ID khách hàng và các tham số lọc như trang, giới hạn và tìm kiếm",
     *     tags={"Expense"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của khách hàng",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Số lượng mục trả về mỗi trang (số lượng trang). Nếu không truyền, sẽ lấy tất cả",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Từ khóa tìm kiếm (có thể tìm theo ngày, loại chi phí hoặc tên dự án)",
     *         @OA\Schema(type="string", example="2024-12-01")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Số trang (trang đầu tiên là 1)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách chi phí theo khách hàng",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(ref="#/components/schemas/ExpensesModel")
     *             ),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total_pages", type="integer", example=5),
     *             @OA\Property(property="total_items", type="integer", example=50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy chi phí cho khách hàng",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Không tìm thấy chi phí cho khách hàng.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Đã xảy ra lỗi hệ thống.")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByCustomer($id, Request $request)
    {
        $data = $this->expensesRepository->getListByCustomer($id, $request->all());
        return Result::success($data);
    }

    public function createByCustomer(Request $request, $id)
    {
        try {
            $data = $this->expensesRepository->createByCustomer($id, $request->all());
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    public function createByProject(Request $request, $id)
    {
        try {
            $data = $this->expensesRepository->createByProject($id, $request->all());
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/expenses",
     *     tags={"Expense"},
     *     summary="Tạo mới Chi phí",
     *     description="Tạo mới Chi phí",
     *     operationId="createExpense",
     *     @OA\RequestBody(
     *         description="Payload to create a new expenses",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/ExpensesModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(
     *                         property="expense_name",
     *                         type="string",
     *                         description="Tên chi phí"
     *                     ),
     *                     @OA\Property(
     *                         property="amount",
     *                         type="number",
     *                         format="float",
     *                         description="Số tiền"
     *                     ),
     *                     @OA\Property(
     *                         property="category",
     *                         type="integer",
     *                         description="Loại chi phí"
     *                     ),
     *                     @OA\Property(
     *                         property="currency",
     *                         type="string",
     *                         description="Tiền tệ"
     *                     ),
     *                     @OA\Property(
     *                         property="file_name",
     *                         type="string",
     *                         format="binary",
     *                         description="File đính kèm"
     *                     ),
     *                 )
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/ExpensesModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Expenses created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ExpensesModel")
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
        try {
            $data = $request->all();
            $expense = $this->expensesRepository->create($data);
            if (!$expense) {
                return Result::fail(static::errorCreateMess);
            }
            return Result::success($expense);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/expenses/{id}",
     *     summary="Lấy thông tin chi tiết về chi phí",
     *     description="Lấy thông tin chi phí theo ID",
     *     tags={"Expense"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của chi phí",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin chi tiết về chi phí",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ExpensesModel"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy chi phí",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Không tìm thấy chi phí.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Đã xảy ra lỗi hệ thống.")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        $expense = $this->expensesRepository->findId($id);
        if (!$expense) {
            return Result::fail(self::errorMess);
        }
        return Result::success($expense);
    }

    /**
     * @OA\Put(
     *     path="/api/expenses/{id}",
     *     summary="Cập nhật Chi phí",
     *     description="Cập nhật chi phí theo ID của nó",
     *     operationId="updateExpense",
     *     tags={"Expense"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của chi phí",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         description="Payload to create a new expenses",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/ExpensesModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(
     *                         property="expense_name",
     *                         type="string",
     *                         description="Tên chi phí"
     *                     ),
     *                     @OA\Property(
     *                         property="amount",
     *                         type="number",
     *                         format="float",
     *                         description="Số tiền"
     *                     ),
     *                     @OA\Property(
     *                         property="category",
     *                         type="integer",
     *                         description="Loại chi phí"
     *                     ),
     *                     @OA\Property(
     *                         property="currency",
     *                         type="string",
     *                         description="Tiền tệ"
     *                     ),
     *                     @OA\Property(
     *                         property="file_name",
     *                         type="string",
     *                         format="binary",
     *                         description="File đính kèm"
     *                     ),
     *                 )
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/ExpensesModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Expense updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ExpensesModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Expense not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="System error")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update($id, Request $request)
    {
        try {
            $updatedExpense = $this->expensesRepository->update($id, $request->all());
            return Result::success($updatedExpense);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/expenses/{id}",
     *     summary="Xóa chi phí",
     *     description="Xóa chi phí theo ID của nó",
     *     tags={"Expense"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the expense to delete",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="error_code", type="integer", example=0),
     *             @OA\Property(property="error_mess", type="string", example="Success"),
     *             @OA\Property(
     *                 property="result",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="expense_name", type="string", example="Transport Cost")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error_code", type="integer", example=1),
     *             @OA\Property(property="error_mess", type="string", example="Invalid expense ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error_code", type="integer", example=1),
     *             @OA\Property(property="error_mess", type="string", example="Expense not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error_code", type="integer", example=1),
     *             @OA\Property(property="error_mess", type="string", example="Internal server error")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $data = $this->expensesRepository->findId($id);
            if (!$data) {
                return Result::fail(self::errorMess);
            }
            $expense = $this->expensesRepository->destroy($id);
            if (!$expense) {
                return Result::fail(self::errorDeleteMess);
            }
            return Result::success($expense);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorMess);
        }
    }

    public function count($id)
    {
        $data = $this->expensesRepository->countByCustomer($id);
        return Result::success($data);;
    }

    /**
     * @OA\Get(
     *     path="/api/expenses/project/{id}",
     *     summary="Lấy danh sách chi phí theo dự án",
     *     description="Lấy danh sách chi phí cho một dự án dựa trên ID dự án và các tham số tìm kiếm",
     *     tags={"Expense"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của dự án",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Số lượng chi phí muốn lấy. Nếu không có, sẽ lấy tất cả.",
     *         @OA\Schema(
     *             type="integer",
     *             example=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Từ khóa tìm kiếm theo tên chi phí hoặc ngày chi phí",
     *         @OA\Schema(
     *             type="string",
     *             example="Chi phí vận chuyển"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách chi phí theo dự án",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ExpensesModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Dự án không tìm thấy"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByProject($id, Request $request)
    {
        $data = $this->expensesRepository->getListByProject($id, $request->all());
        return Result::success($data);
    }

    public function getListByYearProject($id, Request $request)
    {
        $data = $this->expensesRepository->getListByYearProject($id, $request->all());
        return Result::success($data);
    }

    public function getListByYearCustomer($id, Request $request)
    {
        $data =  $this->expensesRepository->getListByYearCustomer($id, $request->all());
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/expenses/filter",
     *     summary="Lọc chi phí",
     *     description="Lọc chi phí",
     *     operationId="filterExpenses",
     *     tags={"Expense"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Số lượng hiển thị",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Trang hiện tại",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="billable",
     *         in="query",
     *         description="Chi phí có thể xuất hóa đơn",
     *         required=false,
     *         @OA\Schema(type="string", example="[true,false]")
     *     ),
     *     @OA\Parameter(
     *         name="invoice",
     *         in="query",
     *         description="Có hóa đơn",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="notInvoice",
     *         in="query",
     *         description="Không có hóa đơn",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="recurring",
     *         in="query",
     *         description="Chi phí định kỳ",
     *         required=false,
     *         @OA\Schema(type="string", example="monthly")
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Năm",
     *         required=false,
     *         @OA\Schema(type="string", example="[2024,2023]")
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="query",
     *         description="Tháng",
     *         required=false,
     *         @OA\Schema(type="string", example="[12,11]")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Danh mục chi phí",
     *         required=false,
     *         @OA\Schema(type="string", example="[1,2,3]")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully filtered",
     *         @OA\JsonContent(ref="#/components/schemas/ExpensesModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function filterByExpense(Request $request)
    {
        $data = $this->expensesRepository->filterByExpense($request->all());
        return Result::success($data);
    }

    public function filterExpenseByProject($id, Request $request)
    {
        $data = $this->expensesRepository->filterExpenseByProject($id, $request->all());
        return Result::success($data);
    }

    public function getListByYear(Request $request)
    {
        $data = $this->expensesRepository->getListByYear($request->all());
        return Result::success($data);
    }
}
