<?php

namespace Modules\Contract\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Contract\Repositories\ContractType\ContractTypeInterface;

class ContractTypeController extends Controller
{
    protected $contractTypeRepository;

    const errorMess = 'Loại hợp đồng không tồn tại';
    const errorCreateMess = 'Tạo loại hợp đồng thất bại';
    const errorUpdateMess = 'Chỉnh sửa loại hợp đồng thất bại';
    const successDeleteMess = 'Xoá loại hợp đồng thành công';
    const errorDeleteMess = 'Xoá loại hợp đồng thất bại';
    public function __construct(ContractTypeInterface $contractTypeRepository)
    {
        $this->contractTypeRepository = $contractTypeRepository;
    }

    public function index(Request $request)
    {
        $data = $this->contractTypeRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->contractTypeRepository->create($request->all());
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    public function show($id)
    {
        $data = $this->contractTypeRepository->findId($id);
        if (!$data) {
            return Result::fail(static::errorMess);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->contractTypeRepository->update($id, $request->all());
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
        $data = $this->contractTypeRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
