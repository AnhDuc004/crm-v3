<?php

namespace Modules\Shp\Repositories\ShpLogistics;

use Illuminate\Support\Facades\Auth;
use Modules\Shp\Entities\ShpLogistic;

class ShpLogisticRepository implements ShpLogisticInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $shpId = $queryData['shp_id'] ?? null;
        $productId = $queryData['product_id'] ?? null;

        $query = ShpLogistic::with('product');

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
        $shpImgae = ShpLogistic::with('product')->find($id);
        if (!$shpImgae) {
            return null;
        }
        return $shpImgae;
    }

    public function create(array $data)
    {
        $shpLogistic = new ShpLogistic($data);
        $shpLogistic->fill($data);
        $shpLogistic->created_by = Auth::id();
        $shpLogistic->save();
        $shpLogistic = ShpLogistic::with('product')->find($shpLogistic->id);
        return $shpLogistic;
    }

    public function update($id, array $data)
    {
        $shpLogistic = ShpLogistic::find($id);

        if (!$shpLogistic) {
            return null;
        }

        $shpLogistic->fill($data);
        $shpLogistic->updated_by = Auth::id();
        $shpLogistic->save();

        $shpLogistic = ShpLogistic::with('product')->find($shpLogistic->id);

        return $shpLogistic;
    }

    public function delete($id)
    {
        $shpLogistic = ShpLogistic::find($id);
        if (!$shpLogistic) {
            return null;
        }
        $shpLogistic->delete();
        return $shpLogistic;
    }
}
