<?php

namespace Modules\Task\Repositories\TaskType;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Result;
use Modules\Task\Entities\TaskType;

class TaskTypeRepository implements TaskTypeInterface
{
    const messageCodeError = 'Loại công việc không tồn tại';

    public function findId($id)
    {
        $taskType = TaskType::find($id);
        if (!$taskType) {
            return Result::fail(static::messageCodeError);
        }
        return Result::success($taskType);
    }

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $name = isset($queryData["name"]) ? $queryData["name"] : null;

        $baseQuery = TaskType::query();
        if ($name) {
            $baseQuery = $baseQuery->where(
                function($q) use ($name){
                    $q->where('name', 'like',  '%' . $name . '%')
                      ->orWhere('code', 'like',  '%' . $name . '%');
                });
        }
        $taskType = $baseQuery->orderBy('created_at', 'desc');

        if($limit > 0){
            $taskType = $baseQuery->paginate($limit);
        }
        else {
            $taskType = $baseQuery->get();
        }
        return Result::success($taskType);
    }

    public function listSelect()
    {
        $taskTypes =  TaskType::where('status', 1)->orderBy('name', 'desc')->select('id', 'name')->get();
        return Result::success($taskTypes);
    }

    public function create($requestData)
    {
        try {
            $taskType =  new TaskType($requestData);
            $taskType->created_by = Auth::user()->id;
            $result =  $taskType->save();
            if (!$result) {
                return Result::fail('Tạo loại công việc thất bại.');
            }
            return Result::success($taskType);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return Result::fail('Tạo loại công việc thất bại.');
        }
    }

    public function update($id, $requestData)
    {
        try {
            $taskType = TaskType::find($id);
            if (!$taskType) {
                return Result::fail(static::messageCodeError);
            }
            $taskType->fill($requestData);
            $taskType->updated_by = Auth::user()->id;
            $result =  $taskType->save();
            if (!$result) {
                return Result::fail('Sửa loại công việc thất bại.');
            }
            return Result::success($taskType);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return Result::fail('Sửa loại công việc thất bại.');
        }
    }

    public function destroy($id)
    {
        try {
            $taskType = TaskType::find($id);
            if (!$taskType) {
                return Result::fail(static::messageCodeError);
            }
            if ($taskType->tasks()->count() > 0) {
                return Result::fail('Loại công việc đã tồn tại Task. Bạn không có quyền xóa');
            }
            $taskType->delete();
            return Result::success();
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return Result::fail('Xóa loại công việc thất bại.');
        }
    }
}
