<?php

namespace Modules\Shp\Repositories\ShpWholesale;

use Illuminate\Support\Facades\Auth;
use Modules\Shp\Entities\ShpWholesale;

class ShpWholesaleRepository implements ShpWholesaleInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $shpId = $queryData['shp_id'] ?? null;
        $productId = $queryData['product_id'] ?? null;

        $query = ShpWholesale::with('product');

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
        $shpWholesale = ShpWholesale::with('product')->find($id);
        if (!$shpWholesale) {
            return null;
        }
        return $shpWholesale;
    }

    public function create(array $data)
    {
        $shpWholesale = new ShpWholesale($data);
        $shpWholesale->fill($data);
        $shpWholesale->created_by = Auth::id();
        $shpWholesale->save();
        return $shpWholesale;
    }

    public function update($id, array $data)
    {
        $shpWholesale = ShpWholesale::find($id)->first();

        if (!$shpWholesale) {
            return null;
        }

        $shpWholesale->fill($data);
        $shpWholesale->updated_by = Auth::id();
        $shpWholesale->save();

        return $shpWholesale;
    }

    public function delete($id)
    {
        $shpWholesale = ShpWholesale::find($id)->first();

        if (!$shpWholesale) {
            return null;
        }

        $shpWholesale->delete();

        return $shpWholesale;
    }
}
