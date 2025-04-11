<?php

namespace Modules\Expense\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Helpers\Result;
use Illuminate\Support\Facades\Log;
use Modules\Expense\Entities\ExpensesCategories;
use Modules\Expense\Repositories\ExpensesCategories\ExpensesCategoriesInterface;

class ExpensesCategoriesController extends Controller
{
    const errorMess = 'Loại chi phí không tồn tại';
    const errorCreateMess = 'Thêm loại chi phí thất bại';
    const errorUpdateMess = 'Sửa loại chi phí thất bại';
    const errorDeleteMess = 'Xóa loại chi phí thất bại';

    const succDeleMess = "Xóa thành công";
    protected $expensesCategoryRepo;

    public function __construct(ExpensesCategoriesInterface $expensesCategoryRepo)
    {
        $this->expensesCategoryRepo = $expensesCategoryRepo;
    }

    public function index(Request $request)
    {
        $data = $this->expensesCategoryRepo->listAll($request->all());
        return Result::success($data);
    }

    /**
     * @OA\Post(
     *     path="/api/expenses-category",
     *     summary="Tạo mới danh mục chi phí",
     *     description="Tạo mới một danh mục chi phí trong cơ sở dữ liệu.",
     *     operationId="createExpensesCategory",
     *     tags={"Expense"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", description="Tên danh mục chi phí", example="Vận chuyển"),
     *             @OA\Property(property="description", type="string", description="Mô tả danh mục chi phí", example="Chi phí vận chuyển hàng hóa")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully created",
     *         @OA\JsonContent(ref="#/components/schemas/ExpensesCategoryModel")
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
    public function store(Request $request)
    {
        $expensesCategory = $this->expensesCategoryRepo->create($request->all());
        return Result::success($expensesCategory);
    }

    public function show($id)
    {
        $expensesCategory = $this->expensesCategoryRepo->findId($id);
        return Result::success($expensesCategory);
    }

    /**
     * @OA\Put(
     *     path="/api/expenses-category/{id}",
     *     summary="Cập nhật danh mục chi phí",
     *     description="Cập nhật một danh mục chi phí trong cơ sở dữ liệu theo ID.",
     *     operationId="updateExpensesCategory",
     *     tags={"Expense"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của danh mục chi phí",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", description="Tên danh mục chi phí", example="Vận chuyển"),
     *             @OA\Property(property="description", type="string", description="Mô tả danh mục chi phí", example="Chi phí vận chuyển hàng hóa")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated",
     *         @OA\JsonContent(ref="#/components/schemas/ExpensesCategoryModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="ExpensesCategory not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $this->expensesCategoryRepo->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorMess);
            }
            return Result::success($data);;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/expenses-category/{id}",
     *     summary="Xóa danh mục chi phí",
     *     description="Xóa một danh mục chi phí khỏi cơ sở dữ liệu theo ID.",
     *     operationId="deleteExpensesCategory",
     *     tags={"Expense"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của danh mục chi phí",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="ExpensesCategory not found"
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
            $expensesCategory = $this->expensesCategoryRepo->destroy($id);
            if (!$expensesCategory) {
                return Result::fail(self::errorMess);
            }
            return Result::success(self::succDeleMess);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorDeleteMess);
        }
    }
}
