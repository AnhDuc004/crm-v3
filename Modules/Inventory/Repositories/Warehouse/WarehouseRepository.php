<?php

namespace Modules\Inventory\Repositories\Warehouse;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Entities\Warehouse;

class WarehouseRepository implements WarehouseInterface
{
    public function findId($id)
    {
        $warehouse = Warehouse::find($id);
        return $warehouse;
    }

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int)$queryData["limit"] : 10;
        $search = isset($queryData["name"]) ? $queryData["name"] : null;
        $location = isset($queryData["location"]) ? $queryData["location"] : null;

        $baseQuery = Warehouse::query()->orderBy('created_at', 'desc');

        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like', '%' . $search . '%');
        }
        if ($location) {
            $baseQuery = $baseQuery->where('location', 'like', '%' . $location . '%');
        }
        if ($limit > 0) {
            $warehouses = $baseQuery->paginate($limit);
        } else {
            $warehouses = $baseQuery->get();
        }
        return $warehouses;
    }

    public function create($request)
    {
        $warehouse = new Warehouse($request);
        $warehouse->created_by = Auth::id();
        $warehouse->save();
        return $warehouse;
    }

    public function update($id, $request)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->updated_by = Auth::id();
        $warehouse->update($request);
        return $warehouse;
    }

    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();
        return $warehouse;
    }
}
