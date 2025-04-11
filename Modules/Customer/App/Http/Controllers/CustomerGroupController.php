<?php

namespace Modules\Customer\App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Result;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Repositories\Customer\CustomerGroupInterface;

class CustomerGroupController extends Controller
{
    protected $groupRepository;
    const errorMess = 'Nhóm khách hàng không tồn tại';
    const errorCreateMess = 'Tạo nhóm khách hàng thất bại';
    const errorUpdateMess = 'Chỉnh sửa nhóm khách hàng thất bại';
    const errorDeleteMess = 'Xoá nhóm khách hàng thất bại';

    public function __construct(CustomerGroupInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function index(Request $request)
    {
        $data = $this->groupRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:200',
        ], [
            'name.*' => 'Tên nhóm không hợp lệ',
        ]);

        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->groupRepository->create($request->all());
            if (!$data) {
                return Result::fail(static::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    public function show($id)
    {
        $data = $this->groupRepository->findId($id);
        if (!$data) {
            return Result::fail(static::errorMess);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:200',
        ], [
            'name.*' => 'Tên nhóm không hợp lệ',
        ]);

        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->groupRepository->findId($id);
            if (!$data) {
                return Result::fail(static::errorMess);
            }
            if (!$this->groupRepository->update($id, $request->all())) {
                return Result::fail(static::errorUpdateMess);
            }
            return Result::success(true);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        $data = $this->groupRepository->destroy($id);
        if (!$data) {
            return Result::fail(static::errorDeleteMess);
        }
        return Result::success($data);
    }
}
