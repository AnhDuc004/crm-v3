<?php
namespace Modules\Task\Repositories\Reminders;

interface ReminderInterface
{
    public function findId($id);

    public function getListByCustomer($id, $request);

    public function getListByExpense($id, $request);

    public function getListByLead($id, $request);

    public function listAll($request);

    public function listSelect();

    public function createByCustomer($id, $request);

    public function createByLead($id, $request);

    public function createByExpense($id, $request);

    public function createByEstimate($id, $request);

    public function update($id, $request);

    public function destroy($id);

    public function getByEstimaste($id, $request);

    public function getListByTicket($id, $request);

    public function createByTicket($id, $request);

    public function getListByProposal($id, $request);

    public function createByProposal($id, $request);

    public function getListByInvoice($id, $request);

    public function createByInvoice($id, $request);

    public function getListByTask($id, $request);

    public function createByTask($id, $request);

    public function getListByCreditNote($id, $request);

    public function createByCreditNote($id, $request);

}
