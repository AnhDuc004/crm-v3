<?php

namespace Modules\Lead\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Lead\Repositories\Lead\LeadIntegrationEmailInterface;

class LeadIntegrationEmailController extends Controller
{
    const messageCodeError = 'Khách hàng tiềm năng không tồn tại';
    const messageCreate = 'Tạo liên kết email với khách hàng tiềm năng thất bại';
    const messageUpdate = 'Cập nhật liên kết email với khách hàng tiềm năng thất bại';
    const messageDelete = 'Xóa liên kết email với khách hàng tiềm năng thất bại';

    protected $leadRepository;

    public function __construct(LeadIntegrationEmailInterface $leadRepository)
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
            'emailid' => 'bail|required|numeric',
            'subject' => 'bail|nullable|string|max:191',
            'body' => 'bail|nullable|string|max:191',

        ], [
            'leadid.required' => 'Chưa nhập id khách hàng',
            'leadid.exists' => 'Khách hàng không hợp lệ',
            'emailid.required' => 'Chưa nhập id email',
            'emailid.numeric' => 'Email id là số >= 0',
            'subject.max' => 'Khóa học không quá 500 ký tự',
            'body.max' => 'Body không quá 191 ký tự',
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->leadRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::messageCreate);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageCreate);
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
            'emailid' => 'bail|required|numeric',
            'subject' => 'bail|nullable|string|max:191',
            'body' => 'bail|nullable|string|max:191',

        ], [
            'leadid.required' => 'Chưa nhập id khách hàng',
            'leadid.exists' => 'Khách hàng không hợp lệ',
            'emailid.required' => 'Chưa nhập id email',
            'emailid.numeric' => 'Email id là số >= 0',
            'subject.max' => 'Khóa học không quá 500 ký tự',
            'body.max' => 'Body không quá 191 ký tự',
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->leadRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::messageUpdate);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageUpdate);
        }
    }

    public function destroy($id)
    {
        $data = $this->leadRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::messageDelete);
        }
        return Result::success($data);
    }
}
