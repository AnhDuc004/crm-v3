<?php

namespace Modules\Inventory\Repositories\InventoryCheckReport;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Inventory\Entities\InventoryCheckReport;

class InventoryCheckReportRepository implements InventoryCheckReportInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $materialId = isset($queryData['material_id']) ? $queryData['material_id'] : null;
        $productId = isset($queryData['product_id']) ? $queryData['product_id'] : null;
        $warehouseId = isset($queryData['warehouse_id']) ? $queryData['warehouse_id'] : null;

        $query = InventoryCheckReport::with('material:id,name', 'product:id,name', 'warehouse:id,name')
            ->orderBy('created_at', 'desc');

        if ($materialId) {
            $query->where('material_id', $materialId);
        }
        if ($productId) {
            $query->where('product_id', $productId);
        }
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        if (isset($queryData['check_date'])) {
            $query->whereDate('check_date', $queryData['check_date']);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $iventory = InventoryCheckReport::with(['warehouse:id,name', 'material:id,name', 'product:id,name'])->find($id);
        return $iventory;
    }

    public function create(array $data)
    {
        $iventory = new InventoryCheckReport($data);
        $iventory->fill($data);
        $iventory->created_by = Auth::id();
        $iventory->save();
        $iventory = InventoryCheckReport::with(['warehouse:id,name', 'material:id,name', 'product:id,name'])->find($iventory->id);
        return $iventory;
    }

    public function update($id, array $data)
    {
        $iventory = InventoryCheckReport::findOrFail($id);
        $iventory->updated_by = Auth::id();
        $iventory->update($data);
        $iventory = InventoryCheckReport::with(['warehouse:id,name', 'material:id,name', 'product:id,name'])->find($iventory->id);
        return $iventory;
    }

    public function delete($id)
    {
        $material = InventoryCheckReport::findOrFail($id);
        $material->delete();
        return $material;
    }
}
