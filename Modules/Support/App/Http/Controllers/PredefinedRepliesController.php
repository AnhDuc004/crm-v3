<?php

namespace Modules\Support\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Support\Repositories\PredefinedReplies\PredefinedRepliesInterface;

class PredefinedRepliesController extends Controller
{
    const errorMess = 'Phản hồi không tồn tại';
    const errorCreateMess = 'Tạo phản hồi thất bại';
    const errorUpdateMess = 'Chỉnh sửa phản hồi thất bại';
    const successDeleteMess = 'Xoá phản hồi thành công';
    const errorDeleteMess = 'Xoá phản hồi thất bại';
    protected $predefinedRepliesRepository;

    public function __construct(PredefinedRepliesInterface $predefinedRepliesRepository)
    {
        $this->predefinedRepliesRepository = $predefinedRepliesRepository;
    }

    public function index(Request $request)
    {
        $data = $this->predefinedRepliesRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->predefinedRepliesRepository->create($request->all());
            if (!$data) {
                Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function show($id)
    {
        $data = $this->predefinedRepliesRepository->findId($id);
        if (!$data) {
            Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    public function update($id, Request $request)
    {
        try {
            $data = $this->predefinedRepliesRepository->update($id, $request->all());
            if (!$data) {
                Result::fail(self::errorUpdateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        $data = $this->predefinedRepliesRepository->destroy($id);
        if (!$data) {
            Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
