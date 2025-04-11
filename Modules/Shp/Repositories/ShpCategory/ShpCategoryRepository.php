<?php

namespace Modules\Shp\Repositories\ShpCategory;

use Illuminate\Support\Facades\Auth;
use Modules\Shp\Entities\ShpCategory;

class ShpCategoryRepository implements ShpCategoryInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['name']) ? $queryData['name'] : null;
        $query = ShpCategory::query();

        if ($search) {
            $query->where('category_name', 'like', '%' . $search . '%');
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $category = ShpCategory::with('products')->find($id);
        if (!$category) {
            return null;
        }
        return $category;
    }

    public function create(array $data)
    {
        $category = new ShpCategory($data);
        $category->fill($data);
        $category->created_by = Auth::id();
        $category->save();
        return $category;
    }

    public function update($id, array $data)
    {
        $category = ShpCategory::find($id);
        if (!$category) {
            return null;
        }
        $category->fill($data);
        $category->updated_by = Auth::id();
        $category->save();

        return $category;
    }

    public function delete($id)
    {
        $category = ShpCategory::find($id);
        if (!$category) {
            return null;
        }
        ShpCategory::where('parent_category_id', $id)->update(['parent_category_id' => null]);
        $category->delete();
        return $category;
    }
}
