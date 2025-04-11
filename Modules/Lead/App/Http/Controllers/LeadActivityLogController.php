<?php

namespace Modules\Lead\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Lead\Repositories\Lead\LeadActivityLogInterface;

class LeadActivityLogController extends Controller
{
    const messageCodeError = 'Nhật ký không tồn tại';
    const messageCreateError = 'Tạo nhật ký thất bại';
    const messageUpdateError = 'Sửa nhật ký thất bại';
    const messageDeleteError = 'Xóa nhật ký thất bại';

    protected $leadRepository;

    public function __construct(LeadActivityLogInterface $leadRepository)
    {
        $this->leadRepository = $leadRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->leadRepository->listAll($request->all()));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'leadid' => 'bail|required|numeric|exists:leads,id',
            'description' => 'bail|nullable|string|max:500',
            'additional_data' => 'bail|nullable|string|max:191',
            'staffid' => 'bail|required|numeric',
            'full_name' => 'bail|nullable|string|max:191',
            'custom_activity' => 'bail|required|integer|in: 0,1'

        ], [
            'name.required' => 'Chưa nhập tên khách hàng',
            'name.max' => 'Tên khách không quá 191 ký tự',
            'description.max' => 'Mô tả không quá 500 ký tự',
            'additional_data.max' => 'Mô tả không quá 191 ký tự',
            'full_name.max' => 'Mô tả không quá 191 ký tự',
            'staffid.required' => 'Chưa nhập Id nhân viên',
            'staffid.numeric' => 'Id nhân viên là số >= 0',
            'custom_activity.in' => 'Hoạt động không hợp lệ'
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->leadRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::messageCreateError);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageCreateError);
        }
    }

    public function show($id)
    {
        $data = $this->leadRepository->findId($id);
        if (!$data) {
            return Result::fail(self::messageCodeError);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'leadid' => 'bail|required|numeric|exists:leads,id',
            'description' => 'bail|nullable|string|max:500',
            'additional_data' => 'bail|nullable|string|max:191',
            'staffid' => 'bail|required|numeric',
            'full_name' => 'bail|nullable|string|max:191',
            'custom_activity' => 'bail|required|integer|in: 0,1'

        ], [
            'leadid.required' => 'Chưa nhập id khách hàng',
            'leadid.exists' => 'Khách hàng không hợp lệ',
            'description.max' => 'Mô tả không quá 500 ký tự',
            'additional_data.max' => 'Mô tả không quá 191 ký tự',
            'full_name.max' => 'Mô tả không quá 191 ký tự',
            'staffid.required' => 'Chưa nhập Id nhân viên',
            'staffid.numeric' => 'Id nhân viên là số >= 0',
            'custom_activity.in' => 'Hoạt động không hợp lệ'
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->leadRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::messageUpdateError);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageUpdateError);
        }
    }

    public function destroy($id)
    {
        $data = $this->leadRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::messageDeleteError);
        }
        return Result::success($data);
    }

    public function getListByLead($id, Request $request)
    {
        return Result::success($this->leadRepository->getListByLead($id, $request->all()));
    }
}
