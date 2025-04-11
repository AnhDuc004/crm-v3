<?php

namespace Modules\Task\Repositories\TaskNote;

interface TaskNoteInterface
{
    public function findId($id);

    public function listAll($queryData = null);

    public function create($requestData);

    public function update($id, $requestData);

    public function destroy($id);

}
