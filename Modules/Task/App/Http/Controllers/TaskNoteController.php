<?php

namespace Modules\Task\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Task\Repositories\TaskNote\TaskNoteInterface;

class TaskNoteController extends Controller
{
    const errorMess = 'Không tìm thấy ghi chú nhiệm vụ.';
    const errorCreateMess = 'Tạo ghi chú nhiệm vụ thất bại.';
    const errorUpdateMess = 'Cập nhật ghi chú nhiệm vụ thất bại.';
    const errorDeleteMess = 'Xóa ghi chú nhiệm vụ thất bại.';
    protected $taskNoteRepository;

    public function __construct(TaskNoteInterface $taskNoteRepository)
    {
        $this->taskNoteRepository = $taskNoteRepository;
    }


    public function index(Request $request)
    {
        $data = $this->taskNoteRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'bail|nullable|string|max:500'
        ], [
            'description.*' => 'Mô tả không quá 500 ký tự'
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->taskNoteRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'bail|nullable|string|max:500',
        ], [
            'description.*' => 'Mô tả không quá 500 ký tự',
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->taskNoteRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorUpdateMess);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        $data = $this->taskNoteRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
