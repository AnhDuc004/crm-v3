<?php

namespace Modules\Task\Repositories\Task;

interface TaskInterface
{
    public function findId($id);

    public function getListByCustomer($id, $request);

    public function getListByExpense($id, $request);


    public function listAll($queryData);

    public function count($queryData);

    public function listByProject($project_id, $request);

    public function listByStaff($project_id,$task_id);
    // list task không thuộc project nào
    public function listOutProject();

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function changeStatus($id, $request);

    public function changePriority($id, $request);

    public function addChecklist($task_id,$request);

    public function destroyChecklist($id);

    public function addComment( $task_id, $request);

    public function updateComment($comment_id ,$request);

    public function destroyComment($comment_id);

    public function addAssignee($request);

    public function destroyAssignee($task_id, $staff_id);

    public function addFollower($request);

    public function destroyFollower($task_id, $staff_id);
    //task phụ thuộc vào lead
    public function getListByLead($id, $request);

    //tast phụ thuộc vào estimate
    public function getListByEstimate($id, $request);

    public function getListByContract($id, $request);

    //task phụ thuộc vào proposal
    public function getListByProposal($id, $request);


    //task phụ thuộc vào Invoice
    public function getListByInvoice($id, $request);

    //summary task theo project
    public function countByProject($id);

    // filter task theo project
    public function filterTaskByProject($id, $request);
    public function filterTaskByProjectId($id, $request);

    //copy data
    public function copyData($id, $request);

    // filter task theo contract
    public function filterTaskByContract($id, $request);

    // Lấy năm của crm
    public function year();

    // Lấy staff theo task
    public function listStaffByTask($task_id);

    // filter task theo lead
    public function filterTaskByLead($id, $request);

    public function destroyTag($task_id, $tag_id);

    //task phụ thuộc vào Ticket
    public function getListByTicket($id, $request);

    // filter task theo ticket
    public function filterTaskByTicket($id, $request);

    // filter task theo ticket
    public function filterTaskByCustomer($id, $request);

    // filter task theo ticket
    public function filterTaskByExpense($id, $request);

    // filter task theo invoice
    public function filterTaskByInvoice($id, $request);

    // filter task theo proposal
    public function filterTaskByProposal($id, $request);

    // bulk action
    public function bulkAction($request);

    // list task theo milestones
    public function listTaskByMilestones($project_id, $request);

    // List task on week
    public function getTasksInCurrentWeek();

    public function countTasksByStatus($request);

    public function taskSummaryByProject($request);

    public function taskSummaryByCustomer($request);

}
