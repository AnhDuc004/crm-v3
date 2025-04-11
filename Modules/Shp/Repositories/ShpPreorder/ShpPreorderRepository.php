<?php

namespace Modules\Shp\Repositories\ShpPreorder;

use Illuminate\Support\Facades\Auth;
use Modules\Shp\Entities\ShpPreorder;

class ShpPreorderRepository implements ShpPreorderInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $shpId = $queryData['shp_id'] ?? null;
        $productId = $queryData['product_id'] ?? null;

        $query = ShpPreorder::with('product');

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
        $shpPreorder = ShpPreorder::with('product')->find($id);
        if (!$shpPreorder) {
            return null;
        }
        return $shpPreorder;
    }

    public function create(array $data)
    {
        $shpPreorder = new ShpPreorder($data);
        $shpPreorder->fill($data);
        $shpPreorder->created_by = Auth::id();
        $shpPreorder->save();
        return $shpPreorder;
    }

    public function update($id, array $data)
    {
        $shpPreorder = ShpPreorder::find($id)->first();

        if (!$shpPreorder) {
            return null;
        }

        $shpPreorder->fill($data);
        $shpPreorder->updated_by = Auth::id();
        $shpPreorder->save();

        return $shpPreorder;
    }

    public function delete($id)
    {
        $shpPreorder = ShpPreorder::find($id);
        if (!$shpPreorder) {
            return null;
        }
        $shpPreorder->delete();
        return $shpPreorder;
    }
}
