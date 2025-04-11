<?php

namespace Modules\Sale\Repositories\Taxes;
use Modules\Sale\Entities\Taxes;

class TaxesRepository implements TaxesInterface
{
    // List taxes theo id
    public function findId($id)
    {
        $taxes = Taxes::find($id);
        if (!$taxes) {
            return null;
        }
        return $taxes;
    }

    // List taxes
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Taxes::query();

        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }
        if ($limit > 0) {
            $taxes = $baseQuery->paginate($limit);
        } else {
            $taxes = $baseQuery->get();
        }

        return $taxes;
    }

    // Thêm mới taxes
    public function create($request)
    {
        $taxes = new Taxes($request);
        $taxes->save();
        return $taxes;
    }

    // Cập nhật taxes
    public function update($id, $request)
    {
        $taxes = Taxes::find($id);
        if (!$taxes) {
            return null;
        }
        $taxes->fill($request);
        $taxes->save();
        return $taxes;
    }

    // Xóa taxes
    public function destroy($id)
    {
        $taxes = Taxes::find($id);
        if (!$taxes) {
            return null;
        }
        $taxes->delete();
        return $taxes;
    }
}
