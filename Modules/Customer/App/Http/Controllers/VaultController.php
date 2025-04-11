<?php

namespace Modules\Customer\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Customer\Repositories\Vault\VaultInterface;

class VaultController extends Controller
{
    const errorMessage = 'Kho tiền không tồn tại';
    const errorCreateMessage = 'Thêm mới thất bại';
    const errDelete = 'Xóa kho tiền thất bại';
    const errorUpdateMessage = 'Cập nhật kho tiền thất bại';

    protected $vaultRepository;

    public function __construct(VaultInterface $vaultRepository)
    {
        $this->vaultRepository = $vaultRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->vaultRepository->listAll($request->all()));
    }

    public function getListByCustomer($id, Request $request)
    {
        return Result::success($this->vaultRepository->getListByCustomer($id, $request->all()));
    }

    public function createByCustomer(Request $request, $id)
    {
        try {
            $data = $this->vaultRepository->createByCustomer($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMessage);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMessage);
        }
    }

    public function show($id)
    {
        $data = $this->vaultRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMessage);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        // $validator = Validator::make($request->all(), [
        //     'name' => 'bail|required|string|max:191',

        // ], [
        //     'name.required'=>'Chưa nhập tên khách hàng',
        //     'name.max'=>'Tên khách không quá 191 ký tự',
        // ]);
        try {
            $data = $this->vaultRepository->findId($id);
            if (!$data) {
                return Result::fail(self::errorUpdateMessage);
            }
            $this->vaultRepository->update($id, $request->all());
            return Result::success();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMessage);
        }
    }

    public function destroy($id)
    {
        $data = $this->vaultRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMessage);
        }
        if (!$this->vaultRepository->destroy($id)) {
            return Result::fail(self::errDelete);
        }
        return Result::success();
    }
}
