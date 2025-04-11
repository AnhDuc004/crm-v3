<?php

namespace Modules\Tik\Repositories\TikSku;

use Illuminate\Support\Facades\Auth;
use Modules\Tik\Entities\TikSku;

class TikSkuRepository implements TikSkuInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $sku_id = $queryData['sku_id'] ?? null;
        $productId = $queryData['product_id'] ?? null;
        $seller_sku = $queryData['seller_sku'] ?? null;

        $query = TikSku::with('product');

        if ($sku_id) {
            $query->where('sku_id', $sku_id);
        }
        if ($productId) {
            $query->where('product_id', $productId);
        }
        if ($seller_sku) {
            $query->where('seller_sku', $seller_sku);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $tikSku = TikSku::with('product')->find($id);
        if (!$tikSku) {
            return null;
        }
        return $tikSku;
    }

    public function create(array $data)
    {
        $tikSku = new TikSku($data);
        $tikSku->fill($data);
        $tikSku->created_by = Auth::id();
        $tikSku->save();
        $tikSku = TikSku::with('product')->find($tikSku->id);
        return $tikSku;
    }

    public function update($id, array $data)
    {
        $tikSku = TikSku::find($id)->first();

        if (!$tikSku) {
            return response()->json(['message' => 'Không tìm thấy thuộc tính'], 404);
        }


        $tikSku->fill($data);
        $tikSku->updated_by = Auth::id();
        $tikSku->save();
        $tikSku = TikSku::with('product')->find($id);
        return $tikSku;
    }

    public function delete($id)
    {
        $tikSku = TikSku::find($id)->first();

        if (!$tikSku) {
            return null;
        }
        $tikSku->delete();
        return $tikSku;
    }
}
