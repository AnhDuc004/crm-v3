<?php

namespace Modules\Sale\Repositories\Payment;
use Modules\Sale\Entities\PaymentMode;

class PaymentModeRepository implements PaymentModeInterface
{
    // List payment-mode theo id
    public function findId($id)
    {
        $paymentMode = PaymentMode::find($id);
        if (!$paymentMode) {
            return null;
        }
        return $paymentMode;
    }

    // List payment-mode
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;

        $baseQuery = PaymentMode::query();
        $paymentMode = $baseQuery->orderBy('created_at', 'desc');

        if ($limit > 0) {
            $paymentMode = $baseQuery->paginate($limit);
        } else {
            $paymentMode = $baseQuery->get();
        }
        return $paymentMode;
    }

    //Thêm mới payment-mode
    public function create($request)
    {
        $paymentMode = new PaymentMode($request);
        $paymentMode->save();
        return $paymentMode;
    }

    // Cập nhật payment-mode
    public function update($id, $request)
    {
        $paymentMode = PaymentMode::find($id);
        if (!$paymentMode) {
            return null;
        }
        $paymentMode->fill($request);
        $paymentMode->save();
        return $paymentMode;
    }

    // Xóa payment-mode
    public function destroy($id)
    {
        $paymentMode = PaymentMode::find($id);
        if (!$paymentMode) {
            return null;
        }
        $paymentMode->delete();
        return $paymentMode;
    }
}
