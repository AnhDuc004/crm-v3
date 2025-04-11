<?php

namespace Modules\Task\Repositories\TaskType;

interface TaskTypeInterface
{
    public function findId($id);

    public function listAll($queryData);

    public function listSelect();

    public function create($requestData);

    public function update($id, $requestData);

    public function destroy($id);
}
