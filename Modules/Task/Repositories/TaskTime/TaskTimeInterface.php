<?php

namespace Modules\Task\Repositories\TaskTime;

interface TaskTimeInterface
{
    public function listByProject($request, $project_id);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function getListByStaff($id, $request);

    public function createByTask($id, $request);

    public function getListByTask($id, $request);

    public function taskTimeByProject($id, $request);

    public function getLoggedTime();

    public function chartLog($id);

}
