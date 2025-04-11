<?php

namespace Modules\Shp\Repositories\ShpGtin;

use Illuminate\Support\Facades\Auth;
use Modules\Shp\Entities\ShpGtin;

class ShpGtinRepository implements ShpGtinInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $shpId = $queryData['shp_id'] ?? null;
        $productId = $queryData['product_id'] ?? null;
        $gtin_code = $queryData['gtin_code'] ?? null;

        $query = ShpGtin::with('product');

        if ($shpId) {
            $query->where('shp_id', $shpId);
        }
        if ($productId) {
            $query->where('product_id', $productId);
        }
        if ($gtin_code) {
            $query->where('gtin_code', $gtin_code);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }
    public function findById($id)
    {
        $shpGtin = ShpGtin::with('product')->find($id);
        if (!$shpGtin) {
            return null;
        }
        return $shpGtin;
    }

    public function create(array $data)
    {
        $shpGtin = new ShpGtin($data);
        $shpGtin->fill($data);
        $shpGtin->created_by = Auth::id();
        $shpGtin->save();
        $shpGtin = ShpGtin::with('product')->find($shpGtin->id);
        return $shpGtin;
    }

    public function update($id, array $data)
    {
        $shpGtin = ShpGtin::find($id)->first();

        if (!$shpGtin) {
            return null;
        }

        $shpGtin->fill($data);
        $shpGtin->updated_by = Auth::id();
        $shpGtin->save();
        $shpGtin = ShpGtin::with('product')->find($shpGtin->id);
        return $shpGtin;
    }

    public function delete($id)
    {
        $shpGtin = ShpGtin::find($id);
        if (!$shpGtin) {
            return null;
        }
        $shpGtin->delete();
        return $shpGtin;
    }
}
