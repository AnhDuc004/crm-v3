<?php

namespace Modules\Customer\App\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Result;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Repositories\Notification\NotificationInterface;

class NotificationController extends Controller
{
    const messageCodeError = 'Notification không tồn tại';
    const messageCreateErr = 'Thêm Notification thất bại';
    const messageUpdateErr = 'Cập nhật Notification thất bại';
    const messageDeleteErr = 'Xóa Notification thất bại';

    protected $notificationRepository;

    public function __construct(NotificationInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->notificationRepository->listByStaff($request->all()));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string',
        ], [
            'name.required' => 'Chưa nhập Notification',
        ]);

        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->notificationRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::messageCreateErr);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageCreateErr);
        }
    }

    public function show($id)
    {
        $data = $this->notificationRepository->findId($id);
        if (!$data) {
            return Result::fail(self::messageCodeError);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string',
        ], [
            'name.required' => 'Chưa nhập Notification',
        ]);

        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }

        try {
            $data = $this->notificationRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::messageUpdateErr);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageUpdateErr);
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->notificationRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::messageDeleteErr);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageDeleteErr);
        }
    }

    public function isRead($id, Request $request)
    {
        return Result::success($this->notificationRepository->isRead($id, $request->all()));
    }
}
