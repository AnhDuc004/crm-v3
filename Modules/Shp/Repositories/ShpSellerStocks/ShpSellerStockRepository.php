<?php

namespace Modules\Shp\Repositories\ShpSellerStocks;

use Illuminate\Support\Facades\Auth;
use Modules\Shp\Entities\ShpSellerStock;

class ShpSellerStockRepository implements ShpSellerStockInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $shpId = $queryData['shp_id'] ?? null;
        $productId = $queryData['product_id'] ?? null;

        $query = ShpSellerStock::with('product');

        if ($shpId) {
            $query->where('shp_id', $shpId);
        }
        if ($productId) {
            $query->where('product_id', $productId);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $shpSellerStock = ShpSellerStock::with('product')->find($id);
        if (!$shpSellerStock) {
            return null;
        }
        return $shpSellerStock;
    }

    public function create(array $data)
    {
        $shpSellerStock = new ShpSellerStock($data);
        $shpSellerStock->fill($data);
        $shpSellerStock->created_by = Auth::id();
        $shpSellerStock->save();
        return $shpSellerStock;
    }

    public function update($id, array $data)
    {
        $shpSellerStock = ShpSellerStock::find($id)->first();

        if (!$shpSellerStock) {
            return null;
        }

        $shpSellerStock->fill($data);
        $shpSellerStock->updated_by = Auth::id();
        $shpSellerStock->save();

        return $shpSellerStock;
    }

    public function delete($id)
    {
        $shpSellerStock = ShpSellerStock::find($id);
        if (!$shpSellerStock) {
            return null;
        }
        $shpSellerStock->delete();
        return $shpSellerStock;
    }
}
