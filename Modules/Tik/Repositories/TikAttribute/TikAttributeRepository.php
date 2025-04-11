<?php

namespace Modules\Tik\Repositories\TikAttribute;

use Illuminate\Support\Facades\Auth;
use Modules\Tik\Entities\TikAttribute;

class TikAttributeRepository implements TikAttributeInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['name']) ? $queryData['name'] : null;
        $query = TikAttribute::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $attribute = TikAttribute::find($id);
        if (!$attribute) {
            return null;
        }
        return $attribute;
    }

    public function create(array $data)
    {
        $attribute = new TikAttribute($data);
        $attribute->fill($data);
        $attribute->created_by = Auth::id();
        $attribute->save();
        return $attribute;
    }

    public function update($id, array $data)
    {
        $attribute = TikAttribute::find($id)->first();

        if (!$attribute) {
            return response()->json(['message' => 'Không tìm thấy thuộc tính'], 404);
        }


        $attribute->fill($data);
        $attribute->updated_by = Auth::id();
        $attribute->save();

        return $attribute;
    }

    public function delete($id)
    {
        $attribute = TikAttribute::find($id)->first();

        if (!$attribute) {
            return null;
        }
        $attribute->delete();
        return $attribute;
    }
}
