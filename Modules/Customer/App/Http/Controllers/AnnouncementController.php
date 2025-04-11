<?php

namespace Modules\Customer\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Repositories\Announcement\AnnouncementInterface;

class AnnouncementController extends Controller
{
    const errorMess = 'Thông báo không tồn tại';
    const errorCreateMess = 'Tạo thông báo thất bại';
    const errorUpdateMess = 'Chỉnh sửa thông báo thất bại';
    const successDeleteMess = 'Xoá thông báo thành công';
    const errorDeleteMess = 'Xoá thông báo thất bại';
    protected $announcementRepository;

    public function __construct(AnnouncementInterface $announcementRepository)
    {
        $this->announcementRepository = $announcementRepository;
    }

    public function index(Request $request)
    {
        $data = $this->announcementRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->announcementRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function show($id)
    {
        try {
            $data = $this->announcementRepository->findId($id);
            if (!$data) {
                return Result::fail(self::errorMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorMess);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $data = $this->announcementRepository->update($id, $request->all());
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
        try {
            $data = $this->announcementRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::errorDeleteMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorDeleteMess);
        }
    }
}
