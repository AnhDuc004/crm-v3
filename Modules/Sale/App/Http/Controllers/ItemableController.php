<?php

namespace Modules\Sale\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Sale\Repositories\Itemable\ItemableInterface;

class ItemableController extends Controller
{
    const errorMess = 'Vật phẩm không tồn tại';
    const errorMessCreate = "Thêm mới vật phẩm thất bại";
    const errorMessUpdate = "Cập nhật vật phẩm thất bại";
    const errorMessDelete = "Xóa vật phẩm thất bại";

    protected $itemableRepository;
    public function __construct(ItemableInterface $itemableRepository)
    {
        $this->itemableRepository = $itemableRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->itemableRepository->listAll($request->all()));
    }

    public function store($requestData)
    {
        try {
            $data = $this->itemableRepository->create($requestData->all());
            if (!$data) {
                return Result::fail(self::errorMessCreate);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail($e->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        try {
            $data = $this->itemableRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorMessUpdate);
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
            $data = $this->itemableRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::errorMessDelete);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail($e->getMessage());
        }
    }

    public function showInvoice($id, Request $request)
    {
        $data = $this->itemableRepository->findInvoice($id, $request->all());
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }
}
