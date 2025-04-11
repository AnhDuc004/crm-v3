<?php

namespace Modules\Task\Repositories\TaskNote;

use Modules\Task\Entities\TaskNote;

class TaskNoteRepository implements TaskNoteInterface
{
    public function findId($id)
    {
        $taskNote = TaskNote::find($id);
        if (!$taskNote) {
            return null;
        }
        return $taskNote;
    }

    public function listAll($queryData = null)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $name = isset($queryData["name"]) ? $queryData["name"] : null;

        $baseQuery = TaskNote::query();
        if ($name) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($name) {
                    $q->where('name', 'like',  '%' . $name . '%')
                        ->orWhere('code', 'like',  '%' . $name . '%');
                }
            );
        }
        $taskNote = $baseQuery->orderBy('created_at', 'desc');

        if ($limit > 0) {
            $taskNote = $baseQuery->paginate($limit);
        } else {
            $taskNote = $baseQuery->get();
        }
        return $taskNote;
    }

    public function create($requestData)
    {
        $taskNote = new TaskNote($requestData);
        $taskNote->save();
        return $taskNote;
    }

    public function update($id, $requestData)
    {
        $taskNote = TaskNote::find($id);
        if (!$taskNote) {
            return null;
        }
        $taskNote->fill($requestData);
        $taskNote->save();
        return $taskNote;
    }

    public function destroy($id)
    {
        $taskNote = TaskNote::find($id);
        if (!$taskNote) {
            return null;
        }
        $taskNote->delete();
        return $taskNote;
    }
}
