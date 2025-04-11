<?php

namespace Modules\Expense\Repositories\ExpensesCategories;

interface ExpensesCategoriesInterface
{
    public function findId($id);
    
    public function listAll($request);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);
}