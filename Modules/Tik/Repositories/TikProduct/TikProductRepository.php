<?php

namespace Modules\Tik\Repositories\TikProduct;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Tik\Entities\TikProduct;

class TikProductRepository implements TikProductInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['name']) ? $queryData['name'] : null;
        $query = TikProduct::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $product = TikProduct::find($id);
        if (!$product) {
            return null;
        }
        return $product;
    }

    public function create(array $data)
    {
        $product = new TikProduct($data);
        $product->fill($data);
        $product->create_time = time();
        $product->created_by = Auth::id();
        $product->save();
        return $product;
    }

    public function update($id, array $data)
    {
        $product = TikProduct::findOrFail($id);
        $product->fill($data);
        $product->update_time = time();
        $product->updated_by = Auth::id();
        $product->save();
        return $product;
    }


    public function delete($id)
    {
        $product = TikProduct::find($id);
        if (!$product) {
            return null;
        }
        $product->delete();
        return $product;
    }
}
