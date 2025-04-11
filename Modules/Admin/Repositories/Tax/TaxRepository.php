<?php

namespace Modules\Admin\Repositories\Tax;
use Modules\Admin\Entities\Tax;

class TaxRepository implements TaxInterface
{
    public function findId($id)
    {
        $tax = Tax::find($id);
        if (!$tax) {
            return null;
        }
        return $tax;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Tax::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%')
                ->orWhere('taxrate', 'like',  '%' . $search . '%');
        }
        if ($limit > 0) {
            $tax = $baseQuery->paginate($limit);
        } else {
            $tax = $baseQuery->get();
        }
        return $tax;
    }

    public function listSelect() {}

    public function create($request)
    {
        $tax = new Tax($request);
        $tax->save();
        return $tax;
    }

    public function update($id, $request)
    {
        $tax = Tax::find($id);
        if (!$tax) {
            return null;
        }
        $tax->fill($request);
        $tax->save();
        return $tax;
    }

    public function destroy($id)
    {
        $tax = Tax::find($id);
        if (!$tax) {
            return null;
        }
        $tax->delete();
        return $tax;
    }
}
