<?php

namespace Modules\Tik\Repositories\TikProductSalesAttribute;

use Illuminate\Support\Facades\Auth;
use Modules\Tik\Entities\TikProductSalesAttribute;

class TikProductSalesAttributeRepository implements TikProductSalesAttributeInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['product_id']) ? $queryData['product_id'] : null;
        $query = TikProductSalesAttribute::query();

        if ($search) {
            $query->where('product_id', 'like', '%' . $search . '%');
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $attribute = TikProductSalesAttribute::find($id);
        if (!$attribute) {
            return null;
        }
        return $attribute;
    }

    public function create(array $data)
    {
        $attribute = new TikProductSalesAttribute($data);
        $attribute->fill($data);
        $attribute->created_by = Auth::id();
        $attribute->save();
        $attribute = TikProductSalesAttribute::with([
            'product:id,name',
            'attribute:id,name',
            'attributeValue:id,name'
        ])->find($attribute->id);
        return $attribute;
    }

    public function update($id, array $data)
    {
        $attribute = TikProductSalesAttribute::find($id);

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
        $attribute = TikProductSalesAttribute::find($id);

        if (!$attribute) {
            return null;
        }
        $attribute->delete();
        return $attribute;
    }
}
