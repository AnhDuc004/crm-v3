<?php

namespace Modules\Inventory\Repositories\InventoryTransaction;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Entities\InventoryTransaction;

class InventoryTransactionRepository implements InventoryTransactionInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $transactionType = isset($queryData['transaction_type']) ? $queryData['transaction_type'] : null;
        $materialId = isset($queryData['material_id']) ? $queryData['material_id'] : null;
        $productId = isset($queryData['product_id']) ? $queryData['product_id'] : null;
        $warehouseId = isset($queryData['warehouse_id']) ? $queryData['warehouse_id'] : null;

        $query = InventoryTransaction::with('warehouse:id,name', 'material:id,name', 'product:id,name')
            ->orderBy('created_at', 'desc');

        if ($transactionType) {
            $query->where('transaction_type', $transactionType);
        }
        if ($materialId) {
            $query->where('material_id', $materialId);
        }
        if ($productId) {
            $query->where('product_id', $productId);
        }
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $iventory = InventoryTransaction::with(['warehouse:id,name', 'material:id,name', 'product:id,name'])->find($id);
        return $iventory;
    }

    public function create(array $data)
    {
        $iventory = new InventoryTransaction($data);
        $iventory->fill($data);
        $iventory->transaction_date = Carbon::now();
        $iventory->created_by = Auth::id();
        $iventory->save();
        $iventory = InventoryTransaction::with(['warehouse:id,name', 'material:id,name', 'product:id,name'])->find($iventory->id);
        return $iventory;
    }

    public function update($id, array $data)
    {
        $iventory = InventoryTransaction::findOrFail($id);
        $iventory->updated_by = Auth::id();
        $iventory->update($data);
        $iventory = InventoryTransaction::with(['warehouse:id,name', 'material:id,name', 'product:id,name'])->find($iventory->id);
        return $iventory;
    }

    public function delete($id)
    {
        $material = InventoryTransaction::findOrFail($id);
        $material->delete();
        return $material;
    }
}
