<?php

namespace Modules\Tik\Repositories\TikCategory;

use Illuminate\Support\Facades\Auth;
use Modules\Tik\Entities\TikCategory;

class TikCategoryRepository implements TikCategoryInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['local_display_name']) ? $queryData['local_display_name'] : null;
        $is_leaf = isset($queryData['is_leaf']) ? $queryData['is_leaf'] : null;
        $query = TikCategory::query();

        if ($search) {
            $query->where('local_display_name', 'like', '%' . $search . '%');
        }

        if (!is_null($is_leaf)) {
            $query->where('is_leaf', '=', filter_var($is_leaf, FILTER_VALIDATE_BOOLEAN));
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }


    public function findById($id)
    {
        $category = TikCategory::find($id);
        if (!$category) {
            return null;
        }
        return $category;
    }

    public function create(array $data)
    {
        $category = new TikCategory($data);
        $category->fill($data);
        $category->created_by = Auth::id();
        $category->save();
        return $category;
    }

    public function update($id, array $data)
    {
        $category = TikCategory::find($id)->first();

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
        $category = TikCategory::find($id)->first();

        if (!$category) {
            return null;
        }
        $category->delete();
        return $category;
    }
}
