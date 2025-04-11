<?php

namespace Modules\Sale\Repositories\PaymentModes;

use Modules\Sale\Entities\PaymentMode;

class PaymentModesRepository implements PaymentModesInterface
{
    // List payment-mode theo id
    public function findId($id)
    {
        $paymentModes = PaymentMode::find($id);
        if (!$paymentModes) {
            return null;
        }
        return $paymentModes;
    }

    // List payment-mode
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = PaymentMode::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }

        if ($limit > 0) {
            $paymentModes = $baseQuery->paginate($limit);
        } else {
            $paymentModes = $baseQuery->get();
        }
        return $paymentModes;
    }

    // Thêm mới payment-mode
    public function create($request)
    {
        $paymentModes = new PaymentMode($request);
        $paymentModes->save();
        return $paymentModes;
    }

    // Cập nhật payment-mode
    public function update($id, $request)
    {
        $paymentModes = PaymentMode::find($id);
        if (!$paymentModes) {
            return null;
        }
        $paymentModes->fill($request);
        $paymentModes->save();
        return $paymentModes;
    }

    // Xóa payment-mode
    public function destroy($id)
    {
        $paymentModes = PaymentMode::find($id);
        if (!$paymentModes) {
            null;
        }
        $data = $paymentModes->delete();
        return $data;
    }

    // Thay đổi active payment-mode
    public function toggleActive($id)
    {
        $paymentMode = PaymentMode::find($id);
        $paymentMode->active = !$paymentMode->active;
        $paymentMode->save();
        return $paymentMode;
    }
}
