<?php

namespace Modules\Sale\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Sale\Repositories\PaymentModes\PaymentModesInterface;

class PaymentModeController extends Controller
{
    const errorMess = 'Ngân hàng không tồn tại';
    const errorCreateMess = "Thêm mới ngân hàng thất bại";
    const errorUpdateMess = "Cập nhật ngân hàng thất bại";
    const errorDeleteMess = "Xóa ngân hàng thất bại";
    protected $paymentModeRepository;

    public function __construct(PaymentModesInterface $paymentModeRepository)
    {
        $this->paymentModeRepository = $paymentModeRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->paymentModeRepository->listAll($request->all()));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'bail|nullable|string|max:500'
        ], [
            'description.*' => 'Mô tả không quá 500 ký tự'
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->paymentModeRepository->create($request->all());
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
            'description' => 'bail|nullable|string|max:500',
        ], [
            'description.*' => 'Mô tả không quá 500 ký tự',
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $this->paymentModeRepository->update($id, $request->all());
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
        try {
            $data = $this->paymentModeRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::errorDeleteMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorDeleteMess);
        }
    }
}
