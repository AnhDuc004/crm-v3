<?php

namespace Modules\Project\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Project\Repositories\ProjectTickets\ProjectTicketsInterface;

class ProjectTicketsController extends Controller
{
    const errorCreateMess = 'Tạo yêu cầu thất bại';
    const errorMess = 'yêu cầu không tồn tại';
    const errorDeleteMess = 'Xóa yêu cầu thất bại';
    const errorUpdateMess = 'Sửa yêu cầu thất bại';
    protected $projectTicketsRepository;

    public function __construct(ProjectTicketsInterface $projectTicketsRepository)
    {
        $this->projectTicketsRepository = $projectTicketsRepository;
    }


    public function index(Request $request)
    {
        return Result::success($this->projectTicketsRepository->listAll($request->all()));
    }

    public function store(Request $request, $project_id)
    {
        $validator = Validator::make($request->all(), [], []);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->projectTicketsRepository->create($project_id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->projectTicketsRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::errorDeleteMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorDeleteMess);
        }
    }

    public function listByProject($project_id, Request $request)
    {
        return Result::success($this->projectTicketsRepository->listByProject($project_id, $request->all()));
    }

    public function update($id, Request $request)
    {
        try {
            $data = $this->projectTicketsRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorUpdateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    public function count($id)
    {
        return Result::success($this->projectTicketsRepository->countByStatus($id));
    }
}
