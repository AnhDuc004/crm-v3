<?php

namespace Modules\Sale\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Sale\Repositories\ItemGroup\ItemGroupInterface;

class ItemGroupController extends Controller
{
    const messageError = 'Nhóm sản phẩm không tồn tại';
    const messageErrorCreate = 'Thêm mới nhóm sản phẩm thất bại';
    const messageErrorUpdate = 'Cập nhật nhóm sản phẩm thất bại';
    const messageErrorDelete = 'Xóa nhóm sản phẩm thất bại';
    protected $itemGroupRepository;

    public function __construct(ItemGroupInterface $itemGroupRepository)
    {
        $this->itemGroupRepository = $itemGroupRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->itemGroupRepository->listAll($request->all()));
    }

    public function store(Request $request)
    {
        try {
            $data = $this->itemGroupRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::messageErrorCreate);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail($e->getMessage());
        }
    }

    public function show($id)
    {
        $data = $this->itemGroupRepository->findId($id);
        if (!$data) {
            return Result::fail(self::messageError);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->itemGroupRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::messageErrorUpdate);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->itemGroupRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::messageErrorDelete);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail($e->getMessage());
        }
    }
}
