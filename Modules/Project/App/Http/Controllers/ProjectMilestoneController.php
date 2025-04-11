<?php

namespace Modules\Project\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Project\Repositories\ProjectMilestone\ProjectMilestoneInterface;

class ProjectMilestoneController extends Controller
{
    const messageCreateError = 'Tạo cột mốc thất bại';
    const messageCreateSuccess = 'Tạo cột mốc thành công';
    const messageCodeError = 'Cột mốc không tồn tại';
    const messageDeleteError = 'Xóa cột mốc thất bại';
    const messageUpdateError = 'Sửa cột mốc thất bại';
    protected $projectMilestoneRepository;

    public function __construct(ProjectMilestoneInterface $projectMilestoneRepository)
    {
        $this->projectMilestoneRepository = $projectMilestoneRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->projectMilestoneRepository->listAll($request->all()));
    }

    public function store(Request $request, $project_id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string',
            'due_date' => 'bail|required|date',
            //'project_id' => 'bail|required|integer',
        ], [
            'name.*' => 'Bạn chưa nhập tên cột mốc',
            'due_date.*' => 'Bạn chưa nhập ngày chốt',
            //'project_id.required' => 'Bạn chưa nhập dự án',

        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->projectMilestoneRepository->create($project_id, $request->all());
            if (!$data) {
                return Result::fail(self::messageCreateError);
            }
            return Result::success(self::messageCreateSuccess, $data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageCreateError);
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->projectMilestoneRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::messageDeleteError);
            }
            return Result::success(self::messageDeleteError);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageDeleteError);
        }
    }

    public function listByProject($project_id, Request $request)
    {
        return Result::success($this->projectMilestoneRepository->listByProject($project_id, $request->all()));
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string',
            'due_date' => 'bail|required|date',
            //'project_id' => 'bail|required|integer',
        ], [
            'name.*' => 'Bạn chưa nhập tên cột mốc',
            'due_date.*' => 'Bạn chưa nhập ngày chốt',
            //'project_id.required' => 'Bạn chưa nhập dự án',

        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->projectMilestoneRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::messageUpdateError);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageUpdateError);
        }
    }
}
