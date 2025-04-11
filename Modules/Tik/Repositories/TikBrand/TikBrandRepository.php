<?php

namespace Modules\Tik\Repositories\TikBrand;

use Illuminate\Support\Facades\Auth;
use Modules\Tik\Entities\TikBrand;

class TikBrandRepository implements TikBrandInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['name']) ? $queryData['name'] : null;
        $query = TikBrand::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $brand = TikBrand::find($id);
        if (!$brand) {
            return null;
        }
        return $brand;
    }

    public function create(array $data)
    {
        $brand = new TikBrand($data);
        $brand->fill($data);
        $brand->created_by = Auth::id();
        $brand->save();
        return $brand;
    }

    public function update($id, array $data)
    {
        $brand = TikBrand::find($id)->first();

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
        $brand = TikBrand::find($id);

        if (!$brand) {
            return null;
        }
        $brand->delete();
        return $brand;
    }
}
