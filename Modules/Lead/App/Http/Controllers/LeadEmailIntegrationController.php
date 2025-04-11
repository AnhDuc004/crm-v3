<?php

namespace Modules\Lead\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Lead\Repositories\Lead\LeadEmailIntegrationInterface;

class LeadEmailIntegrationController extends Controller
{
    const messageCodeError = 'Khách hàng tiềm năng không tồn tại';
    const messageCreate = 'Thêm khách hàng tiềm năng thất bại';
    const messageUpdate = 'Cập nhật khách hàng tiềm năng thất bại';
    const messageDelete = 'Xóa khách hàng tiềm năng thất bại';

    protected $leadRepository;

    public function __construct(LeadEmailIntegrationInterface $leadRepository)
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
            'email' => 'bail|required|email',
            'imap_server' => 'bail|required|string|max:191',
            'responsible' => 'bail|required|numeric',
            'folder' => 'bail|required|string|max:191',
            'encryption' => 'bail|nullable|string|max:191',

        ], [
            'email.required' => 'Chưa nhập email',
            'email.email' => 'Email không hợp lệ',
            'imap_server.required' => 'Chưa nhập server',
            'imap_server.max' => 'Server không quá 191 ký tự',
            'folder.required' => 'Chưa nhập thư mục',
            'folder.max' => 'Thư mục không quá 191 ký tự',
            'responsible.required' => 'Chưa nhập responsible',
            'responsible.numeric' => 'Responsible là số >= 0',
            'encryption.max' => 'Mã hóa không quá 191 ký tự',
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
            'email' => 'bail|required|email',
            'imap_server' => 'bail|required|string|max:191',
            'responsible' => 'bail|required|numeric',
            'folder' => 'bail|required|string|max:191',
            'encryption' => 'bail|nullable|string|max:191',

        ], [
            'email.required' => 'Chưa nhập email',
            'email.email' => 'Email không hợp lệ',
            'imap_server.required' => 'Chưa nhập server',
            'imap_server.max' => 'Server không quá 191 ký tự',
            'folder.required' => 'Chưa nhập thư mục',
            'folder.max' => 'Thư mục không quá 191 ký tự',
            'responsible.required' => 'Chưa nhập responsible',
            'responsible.numeric' => 'Responsible là số >= 0',
            'encryption.max' => 'Mã hóa không quá 191 ký tự',
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
