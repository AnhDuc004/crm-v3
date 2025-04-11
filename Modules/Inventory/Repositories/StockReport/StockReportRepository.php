<?php

namespace Modules\Inventory\Repositories\StockReport;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Entities\InventoryTransaction;
use Modules\Inventory\Entities\StockReport;

class StockReportRepository implements StockReportInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['name']) ? $queryData['name'] : null;
        $productId = isset($queryData['product_id']) ? $queryData['product_id'] : null;
        $materialId = isset($queryData['material_id']) ? $queryData['material_id'] : null;
        $warehouseId = isset($queryData['warehouse_id']) ? $queryData['warehouse_id'] : null;
        $query = StockReport::with('material:id,name', 'product:id,name', 'warehouse:id,name')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        if ($productId) {
            $query->where('product_id', $productId);
        }
        if ($materialId) {
            $query->where('material_id', $materialId);
        }
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }


    public function findById($id)
    {
        $stockReport = StockReport::with(['product:id,name', 'material:id,name', 'warehouse:id,name'])->find($id);
        return $stockReport;
    }

    public function create(array $data)
    {
        $stockReport = new StockReport($data);
        $stockReport->fill($data);
        $stockReport->created_by = Auth::id();
        $stockReport->report_date = Carbon::now();
        $stockReport->save();
        $stockReport = StockReport::with(['product:id,name', 'material:id,name', 'warehouse:id,name'])->find($stockReport->id);
        return $stockReport;
    }

    public function getInventoryTotals($request)
    {
        $warehouseId = $request['warehouse_id'];
        $materialId = $request['material_id'] ?? null;
        $productId = $request['product_id'] ?? null;

        $inQuery = InventoryTransaction::where('warehouse_id', $warehouseId)
            ->where('transaction_type', '0');

        if ($materialId) {
            $inQuery->where('material_id', $materialId);
        } else if ($productId) {
            $inQuery->where('product_id', $productId);
        }

        $totalIn = $inQuery->sum('quantity');

        $outQuery = InventoryTransaction::where('warehouse_id', $warehouseId)
            ->where('transaction_type', '1');

        if ($materialId) {
            $outQuery->where('material_id', $materialId);
        } else if ($productId) {
            $outQuery->where('product_id', $productId);
        }

        $totalOut = $outQuery->sum('quantity');
        $stockBalance = $totalIn - $totalOut;
        $result = [
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'stock_balance' => $stockBalance
        ];
        return $result;
    }

    public function update($id, array $data)
    {
        $productionNorm = StockReport::findOrFail($id);
        $productionNorm->updated_by = Auth::id();
        $productionNorm->update($data);
        $productionNorm = StockReport::with(['product:id,name', 'material:id,name', 'warehouse:id,name'])->find($productionNorm->id);
        return $productionNorm;
    }

    public function delete($id)
    {
        $productionNorm = StockReport::findOrFail($id);
        $productionNorm->delete();
        return $productionNorm;
    }
}
