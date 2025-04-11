<?php

namespace Modules\Sale\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Sale\Repositories\Taxes\TaxesInterface;

class TaxesController extends Controller
{
    const errorMess = 'Thuế không tồn tại';
    const errorCreateMess = "Thêm mới thuế thất bại";
    const errorUpdateMess = "Cập nhật thuế thất bại";
    const errorDeleteMess = "Xóa thuế thất bại";
    protected $taxesRepository;

    public function __construct(TaxesInterface $taxesRepository)
    {
        $this->taxesRepository = $taxesRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->taxesRepository->listAll($request->all()));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:500'
        ], [
            'name.*' => 'Tên không quá 500 ký tự'
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->taxesRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:500',
        ], [
            'name.*' => 'Tên không quá 500 ký tự',
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->taxesRepository->update($id, $request->all());
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
        $data = $this->taxesRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
