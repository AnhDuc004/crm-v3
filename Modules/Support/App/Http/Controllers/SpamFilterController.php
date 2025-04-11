<?php

namespace Modules\Support\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Support\Repositories\SpamFilter\SpamFilterInterface;

class SpamFilterController extends Controller
{
    const errorMess = 'Bộ lọc không tồn tại';
    const errorCreateMess = 'Tạo bộ lọc thất bại';
    const errorUpdateMess = 'Chỉnh sửa bộ lọc thất bại';
    const successDeleteMess = 'Xoá bộ lọc thành công';
    const errorDeleteMess = 'Xoá bộ lọc thất bại';
    protected $spamFilterRepository;

    public function __construct(SpamFilterInterface $spamFilterRepository)
    {
        $this->spamFilterRepository = $spamFilterRepository;
    }

    public function index(Request $request)
    {
        $data = $this->spamFilterRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->spamFilterRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function show($id)
    {
        $data = $this->spamFilterRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    public function update($id, Request $request)
    {
        try {
            $data = $this->spamFilterRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorUpdateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        $data = $this->spamFilterRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }

    public function createByLead(Request $request)
    {
        try {
            $data = $this->spamFilterRepository->createByLead($request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }
}
