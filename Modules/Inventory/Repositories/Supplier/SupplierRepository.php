<?php

namespace Modules\Inventory\Repositories\Supplier;

use Modules\Inventory\Entities\Supplier;
use Illuminate\Support\Facades\Auth;

class SupplierRepository implements SupplierInterface
{
    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) ? (int) $queryData["limit"] : 10;
        $search = isset($queryData["search"]) ? $queryData["search"] : null;
        $query = Supplier::query()->orderBy('created_at', 'desc');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($limit > 0) {
            return $query->paginate($limit);
        }

        return $query->get();
    }

    public function create($request)
    {
        $supplier = new Supplier($request);
        $supplier->created_by = Auth::id();
        $supplier->save();
        return $supplier;
    }

    public function update($id, $request)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->updated_by = Auth::id();
        $supplier->update($request);
        return $supplier;
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return $supplier;
    }

    public function findId($id)
    {
        $supplier = Supplier::find($id);
        return $supplier;
    }
}
