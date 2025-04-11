<?php

namespace Modules\Project\Repositories\Project;

interface ProjectInterface
{
    public function findId($id);

    public function getListByCustomer($id, $request);

    public function listAll($request);

    public function listSelect();

    public function create($request);

    // hàm này thực chất là hàm creat a project
    // tạm thời comment chờ phản hồi từ FE sau đó sẽ xóa
    // public function createByCustomer($id,$request);

    public function update($id, $request);

    public function destroy($id);

    public function copy($id, $request);

    public function countByCustomer($id);

    public function getListByStaff($id, $request);

    public function countOverview($id);

    public function countDayLeft($id);

    public function countByStatus();

    public function getContactByProject($id);

    public function addMember($request, $project_id);

    public function destroyMember($project_id, $staff_id);

    public function bulkAction($request);
}
