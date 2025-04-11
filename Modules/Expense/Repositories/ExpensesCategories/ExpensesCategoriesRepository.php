<?php

namespace Modules\Expense\Repositories\ExpensesCategories;

use App\Helpers\Result;
use Modules\Expense\Entities\ExpensesCategories;

class ExpensesCategoriesRepository implements ExpensesCategoriesInterface
{

    // List expenses-categories theo id
    public function findId($id)
    {
        $expensesCategories = ExpensesCategories::find($id);
        return $expensesCategories;
    }
    // List expenses-categories
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = ExpensesCategories::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }

        if ($limit > 0) {
            $expensesCategories = $baseQuery->paginate($limit);
        } else {
            $expensesCategories = $baseQuery->get();
        }

        return $expensesCategories;
    }
    // Thêm expenses-categories
    public function create($request)
    {
        $expensesCategories = new ExpensesCategories($request);
        $expensesCategories->save();
        return $expensesCategories;
    }

    // Cập nhật expenses-categories
    public function update($id, $request)
    {
        $expensesCategories = ExpensesCategories::find($id);
        $expensesCategories->fill($request);
        $expensesCategories->save();
        return $expensesCategories;
    }
    // Xóa expenses-categories
    public function destroy($id)
    {
        $expensesCategories = ExpensesCategories::find($id);
        $expensesCategories->delete();
        return $expensesCategories;
    }
}
