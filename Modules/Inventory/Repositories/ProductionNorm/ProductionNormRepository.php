<?php

namespace Modules\Inventory\Repositories\ProductionNorm;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Entities\ProductionNorm;

class ProductionNormRepository implements ProductionNormInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['name']) ? $queryData['name'] : null;
        $productId = isset($queryData['product_id']) ? $queryData['product_id'] : null;
        $materialId = isset($queryData['material_id']) ? $queryData['material_id'] : null;
        $season = isset($queryData['season']) ? $queryData['season'] : null;

        $query = ProductionNorm::with('product:id,name', 'material:id,name')->orderBy('created_at', 'desc');
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        if ($productId) {
            $query->where('product_id', $productId);
        }
        if ($materialId) {
            $query->where('material_id', $materialId);
        }
        if ($season) {
            $query->where('season', 'like', '%' . $season . '%');
        }
        return $query->paginate($limit, ['*'], 'page', $page);
    }


    public function findById($id)
    {
        $productionNorm = ProductionNorm::with(['product:id,name', 'material:id,name'])->find($id);
        return $productionNorm;
    }

    public function create(array $data)
    {
        $productionNorm = new ProductionNorm($data);
        $productionNorm->fill($data);
        $productionNorm->created_by = Auth::id();
        $productionNorm->save();
        $productionNorm = ProductionNorm::with(['product:id,name', 'material:id,name'])->find($productionNorm->id);
        return $productionNorm;
    }

    public function update($id, array $data)
    {
        $productionNorm = ProductionNorm::findOrFail($id);
        $productionNorm->updated_by = Auth::id();
        $productionNorm->update($data);
        $productionNorm = ProductionNorm::with(['product:id,name', 'material:id,name'])->find($productionNorm->id);
        return $productionNorm;
    }

    public function delete($id)
    {
        $productionNorm = ProductionNorm::findOrFail($id);
        $productionNorm->delete();
        return $productionNorm;
    }
}
