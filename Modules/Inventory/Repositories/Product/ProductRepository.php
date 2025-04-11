<?php

namespace Modules\Inventory\Repositories\Product;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Entities\Product;

class ProductRepository implements ProductInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['name']) ? $queryData['name'] : null;
        $unitId = isset($queryData['unit_id']) ? $queryData['unit_id'] : null;
        $query = Product::with('unit:id,name')->orderBy('created_at', 'desc');
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $product = Product::with(['unit:id,name'])->find($id);
        return $product;
    }
    public function listSelect()
    {
        return Product::select('name', 'id')->get();
    }

    public function create(array $data)
    {
        $product = new Product($data);
        $product->fill($data);
        $product->created_by = Auth::id();
        $product->save();
        $product = Product::with(['unit:id,name'])->find($product->id);
        return $product;
    }

    public function update($id, array $data)
    {
        $product = Product::findOrFail($id);
        $product->updated_by = Auth::id();
        $product->update($data);
        $product = Product::with(['unit:id,name'])->find($product->id);
        return $product;
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return $product;
    }
}
