<?php

namespace Modules\Expense\Repositories\Expenses;

interface ExpensesInterface
{
    public function findId($id);

    public function getListByCustomer($id, $request);

    public function listAll($request);

    public function create($request);

    public function createByCustomer($id,$request);

    public function createByProject($id,$request);

    public function update($id, $request);

    public function destroy($id);

    public function countByCustomer($id);

    public function getListByProject($id,$request);

    public function getListByYearProject($id,$request); 

    public function getListByYearCustomer($id,$request); 
    
    public function filterByExpense($request);

    public function filterExpenseByProject($id, $request);

    public function getListByYear($request);

}
