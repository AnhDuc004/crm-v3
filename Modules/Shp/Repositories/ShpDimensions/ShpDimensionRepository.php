<?php

namespace Modules\Shp\Repositories\ShpDimensions;

use Illuminate\Support\Facades\Auth;
use Modules\Shp\Entities\ShpDimension;

class ShpDimensionRepository implements ShpDimensionInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $shpId = $queryData['shp_id'] ?? null;
        $productId = $queryData['product_id'] ?? null;

        $query = ShpDimension::with('product');

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
        $dimension = ShpDimension::with('product')->find($id);
        if (!$dimension) {
            return null;
        }
        return $dimension;
    }

    public function create(array $data)
    {
        $dimension = new ShpDimension($data);
        $dimension->fill($data);
        $dimension->created_by = Auth::id();
        $dimension->save();
        return $dimension;
    }
    
    public function update($id, array $data)
    {
        $dimension = ShpDimension::find($id)->first();

        if (!$dimension) {
            return null;
        }

        $dimension->fill($data);
        $dimension->updated_by = Auth::id();
        $dimension->save();

        return $dimension;
    }

    public function delete($id)
    {
        $dimension = ShpDimension::find($id);
        if (!$dimension) {
            return null;
        }
        $dimension->delete();
        return $dimension;
    }
}
