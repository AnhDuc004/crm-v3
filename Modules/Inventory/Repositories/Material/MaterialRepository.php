<?php

namespace Modules\Inventory\Repositories\Material;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Entities\Material;
use Modules\Inventory\Repositories\Material\MaterialInterface;

class MaterialRepository implements MaterialInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['name']) ? $queryData['name'] : null;
        $unitId = isset($queryData['unit_id']) ? $queryData['unit_id'] : null;
        $supplierId = isset($queryData['supplier_id']) ? $queryData['supplier_id'] : null;
        $query = Material::with('supplier:id,name', 'unit:id,name')->orderBy('created_at', 'desc');
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }


        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function listSelect()
    {
        return Material::select('id', 'name')->get();
    }

    public function findById($id)
    {
        $material = Material::with(['unit:id,name', 'supplier:id,name'])->find($id);
        return $material;
    }

    public function create(array $data)
    {
        $material = new Material($data);
        $material->fill($data);
        $material->created_by = Auth::id();
        $material->save();
        $material = Material::with(['unit:id,name', 'supplier:id,name'])->find($material->id);
        return $material;
    }

    public function update($id, array $data)
    {
        $material = Material::findOrFail($id);
        $material->updated_by = Auth::id();
        $material->update($data);
        $material = Material::with(['unit:id,name', 'supplier:id,name'])->find($material->id);
        return $material;
    }

    public function delete($id)
    {
        $material = Material::findOrFail($id);
        $material->delete();
        return $material;
    }
}
