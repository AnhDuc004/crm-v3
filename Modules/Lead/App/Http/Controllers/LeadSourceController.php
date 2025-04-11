<?php

namespace Modules\Lead\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Lead\Repositories\Lead\LeadSourceInterface;
use Illuminate\Support\Facades\Log;

class LeadSourceController extends Controller
{
    protected $leadSourceRepo;
    const messageCodeError = 'Nguồn không tồn tại';
    const messageCreate = 'Tạo Nguồn thất bại';
    const messageUpdate = 'Cập nhật Nguồn thất bại';
    const messageDelete = 'Xóa Nguồn thất bại';
    const messageError = 'Xảy ra lỗi';

    public function __construct(LeadSourceInterface $leadSourceRepo)
    {
        $this->leadSourceRepo = $leadSourceRepo;
    }

    public function index(Request $request)
    {
        try {
            $leadSource = $this->leadSourceRepo->listAll($request->all());
            return Result::success($leadSource);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageError);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:191',
        ], [
            'name.required' => 'Chưa nhập tên nguồn',
            'name.max' => 'Tên nguồn không quá 191 ký tự',
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $request->all();
            $leadSource = $this->leadSourceRepo->create($data);
            if (!$leadSource) {
                return Result::fail(static::messageCreate);
            }
            return Result::success($leadSource);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageCreate);
        }
    }

    public function show($id)
    {
        try {
            $leadSource = $this->leadSourceRepo->findId($id);
            if (!$leadSource) {
                return Result::fail(static::messageCodeError);
            }
            return Result::success($leadSource);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageCodeError);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:191',
        ], [
            'name.required' => 'Chưa nhập tên nguồn',
            'name.max' => 'Tên nguồn không quá 191 ký tự',
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $request->all();
            $leadSource = $this->leadSourceRepo->update($id, $data);
            if (!$leadSource) {
                return Result::fail(static::messageUpdate);
            }
            return Result::success($leadSource);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageUpdate);
        }
    }

    public function destroy($id)
    {
        try {
            $leadSource = $this->leadSourceRepo->destroy($id);
            if (!$leadSource) {
                return Result::fail(static::messageDelete);
            }
            return Result::success($leadSource);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageDelete);
        }
    }
}
