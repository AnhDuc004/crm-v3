<?php
namespace Modules\Shp\Repositories\ShpTaxInfo;

use Illuminate\Support\Facades\Auth;
use Modules\Shp\Entities\ShpTaxInfo;

class ShpTaxInfoRepository implements ShpTaxInfoInterface{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $shpId = $queryData['shp_id'] ?? null;
        $productId = $queryData['product_id'] ?? null;

        $query = ShpTaxInfo::with('product');

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
        $shpTaxInfo = ShpTaxInfo::with('product')->find($id);
        if (!$shpTaxInfo) {
            return null;
        }
        return $shpTaxInfo;
    }

    public function create(array $data)
    {
        $shpTaxInfo = new ShpTaxInfo($data);
        $shpTaxInfo->fill($data);
        $shpTaxInfo->created_by = Auth::id();
        $shpTaxInfo->save();
        return $shpTaxInfo;
    }

    public function update($id, array $data)
    {
        $shpTaxInfo = ShpTaxInfo::find($id)->first();

        if (!$shpTaxInfo) {
            return null;
        }

        $shpTaxInfo->fill($data);
        $shpTaxInfo->updated_by = Auth::id();
        $shpTaxInfo->save();

        return $shpTaxInfo;
    }
    
    public function delete($id)
    {
        $shpTaxInfo = ShpTaxInfo::find($id)->first();
        if (!$shpTaxInfo) {
            return null;
        }
        $shpTaxInfo->delete();
        return $shpTaxInfo;
    }
}