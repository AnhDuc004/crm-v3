<?php

namespace Modules\Shp\Repositories\ShpBrand;
use Illuminate\Support\Facades\Auth;
use Modules\Shp\Entities\ShpBrand;

class ShpBrandRepository implements ShpBrandInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['name']) ? $queryData['name'] : null;
        $query = ShpBrand::query();

        if ($search) {
            $query->where('original_brand_name', 'like', '%' . $search . '%');
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $brand = ShpBrand::find($id);
        if (!$brand) {
            return null;
        }
        return $brand;
    }

    public function create(array $data)
    {
        $brand = new ShpBrand($data);
        $brand->fill($data);
        $brand->created_by = Auth::id();
        $brand->save();
        return $brand;
    }

    public function update($id, array $data)
    {
        $brand = ShpBrand::find($id);
        if (!$brand) {
            return null;
        }
        $brand->fill($data);
        $brand->updated_by = Auth::id();
        $brand->save();

        return $brand;
    }

    public function delete($id)
    {
        $brand = ShpBrand::find($id);
        if (!$brand) {
            return null;
        }
        $brand->delete();
        return $brand;
    }
}
