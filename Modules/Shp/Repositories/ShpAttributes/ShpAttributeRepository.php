<?php

namespace Modules\Shp\Repositories\ShpAttributes;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Shp\Entities\ShpAttribute;

class ShpAttributeRepository implements ShpAttributeInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['filter']) ? $queryData['filter'] : null;
        $query = ShpAttribute::query();

        if ($search) {
            $query->where('original_value_name', 'like', '%' . $search . '%');
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $attribute = ShpAttribute::find($id);
        if (!$attribute) {
            return null;
        }
        return $attribute;
    }

    public function create(array $data)
    {
        $attribute = new ShpAttribute($data);
        $attribute->fill($data);
        $attribute->created_by = Auth::id();
        $attribute->save();
        return $attribute;
    }

    public function update($id, array $data)
    {
        $attribute = ShpAttribute::find($id)->first();

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
        $attribute = ShpAttribute::find($id)->first();

        if (!$attribute) {
            return null;
        }
        $attribute->delete();
        return $attribute;
    }
}
