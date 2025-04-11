<?php

namespace Modules\Tik\Repositories\TikAttributeValue;

use Illuminate\Support\Facades\Auth;
use Modules\Tik\Entities\TikAttributeValue;

class TikAttributeValueRepository implements TikAttributeValueInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['name']) ? $queryData['name'] : null;
        $query = TikAttributeValue::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $attribute = TikAttributeValue::with('attribute')->find($id);
        if (!$attribute) {
            return null;
        }
        return $attribute;
    }

    public function create(array $data)
    {
        $attribute = new TikAttributeValue($data);
        $attribute->fill($data);
        $attribute->created_by = Auth::id();
        $attribute->save();
        $attribute = TikAttributeValue::with('attribute')->find($attribute->id);
        return $attribute;
    }

    public function update($id, array $data)
    {
        $attribute = TikAttributeValue::find($id);

        if (!$attribute) {
            return null;
        }
        $attribute->fill($data);
        $attribute->updated_by = Auth::id();
        $attribute->save();
        $attribute = TikAttributeValue::with('attribute')->find($attribute->id);
        return $attribute;
    }

    public function delete($id)
    {
        $attribute = TikAttributeValue::find($id);

        if (!$attribute) {
            return null;
        }
        $attribute->delete();
        return $attribute;
    }
}
