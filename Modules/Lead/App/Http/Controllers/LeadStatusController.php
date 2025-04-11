<?php

namespace Modules\Lead\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Lead\Repositories\Lead\LeadStatusInterface;
use Illuminate\Support\Facades\Log;

class LeadStatusController extends Controller
{
    protected $leadStatusRepo;
    const messageCodeError = 'Trạng thái khách hàng không tồn tại';
    const messageCreate = 'Tạo Trạng thái khách hàng thất bại';
    const messageUpdate = 'Cập nhật Trạng thái khách hàng thất bại';
    const messageDelete = 'Xóa Trạng thái khách hàng thất bại';
    const messageError = 'Xảy ra lỗi';

    public function __construct(LeadStatusInterface $leadStatusRepo)
    {
        $this->leadStatusRepo = $leadStatusRepo;
    }

    public function index(Request $request)
    {
        try {
            $leadStatus = $this->leadStatusRepo->listAll($request->all());
            return Result::success($leadStatus);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageError);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:191',
            'status_order' => 'bail|nullable|numeric',
            'color' => 'bail|nullable|string|max:191',
            'is_default' => 'bail|required|integer|in: 0,1'

        ], [
            'name.required' => 'Chưa nhập tên trạng thái',
            'name.max' => 'Tên trạng thái không quá 191 ký tự',
            'is_default.required' => 'Bạn chưa chọn trạng thái',
            'is_default.in' => 'Giới tính không hợp lệ',
            'color.*' => 'Màu sắc không quá 191 ký tự',
            'status_order.numeric' => 'Order là số >=0'
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $request->all();
            $leadStatus = $this->leadStatusRepo->create($data);
            if (!$leadStatus) {
                return Result::fail(self::messageCreate);
            }
            return Result::success($leadStatus);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageCreate);
        }
    }

    public function show($id)
    {
        try {
            $leadStatus = $this->leadStatusRepo->findId($id);
            if (!$leadStatus) {
                return Result::fail(self::messageCodeError);
            }
            return Result::success($leadStatus);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageCodeError);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:191',
            'status_order' => 'bail|nullable|numeric',
            'color' => 'bail|nullable|string|max:191',
            'is_default' => 'bail|required|integer|in: 0,1'
        ], [
            'name.required' => 'Chưa nhập tên trạng thái',
            'name.max' => 'Tên trạng thái không quá 191 ký tự',
            'is_default.required' => 'Bạn chưa chọn trạng thái',
            'is_default.in' => 'Giới tính không hợp lệ',
            'color.*' => 'Màu sắc không quá 191 ký tự',
            'status_order.numeric' => 'Order là số >=0'
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $request->all();
            $leadStatus = $this->leadStatusRepo->update($id, $data);
            if (!$leadStatus) {
                return Result::fail(self::messageUpdate);
            }
            return Result::success($leadStatus);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageUpdate);
        }
    }

    public function destroy($id)
    {
        try {
            $leadStatus = $this->leadStatusRepo->destroy($id);
            if (!$leadStatus) {
                return Result::fail(self::messageDelete);
            }
            return Result::success($leadStatus);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageDelete);
        }
    }
}
