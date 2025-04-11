<?php

namespace Modules\Shp\Repositories\ShpProduct;

use Illuminate\Support\Facades\Auth;
use Modules\Shp\Entities\ShpProduct;

class ShpProductRepository implements ShpProductInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['name']) ? $queryData['name'] : null;
        $query = ShpProduct::query();

        if ($search) {
            $query->where('product_name', 'like', '%' . $search . '%');
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $product = ShpProduct::with('category:id,category_name')->find($id);
        if (!$product) {
            return null;
        }
        return $product;
    }

    public function create(array $data)
    {
        $product = new ShpProduct();
        $product->fill($data);
        $product->created_by = Auth::id();
        $product->save();

        return $product;
    }


    public function update($id, array $data)
    {
        $product = ShpProduct::findOrFail($id);
        $product->fill($data);
        $product->updated_by = Auth::id();
        $product->save();
        return $product;
    }


    public function delete($id)
    {
        $product = ShpProduct::find($id);
        if (!$product) {
            return null;
        }
        $product->delete();
        return $product;
    }
}
