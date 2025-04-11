<?php

namespace Modules\Project\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Project\Repositories\ProjectActivity\ProjectActivityInterface;

class ProjectActivityController extends Controller
{

    const errorMess = 'Nhật ký không tồn tại';
    const errorDeleteMess = 'Xóa nhật ký thất bại';
    protected $projectActivityRepository;

    public function __construct(ProjectActivityInterface $projectActivityRepository)
    {
        $this->projectActivityRepository = $projectActivityRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->projectActivityRepository->listAll($request->all()));
    }

    public function getListByProject($id, Request $request)
    {
        return Result::success($this->projectActivityRepository->getListByProject($id, $request->all()));
    }

    public function delete($id)
    {
        $data = $this->projectActivityRepository->destroy($id);
        if ($data) {
            return Result::success(self::errorDeleteMess);
        }
        return Result::fail(self::errorDeleteMess);
    }
}
