<?php

namespace Modules\Sale\Repositories\Item;

use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Illuminate\Support\Facades\Auth;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Sale\Entities\Item;

class ItemRepository implements ItemInterface
{
    use LogActivityTrait;

    public function findId($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return null;
        }
        return $item;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int)$request["limit"] : 10;

        $baseQuery = Item::query()->with(
            'customFields:id,field_to,name',
            'customFieldsValues'
        );

        if ($limit > 0) {
            $item = $baseQuery->paginate($limit);
        } else {
            $item = $baseQuery->limit(1000)->get();
        }
        return $item;
    }

    public function listSelect() {}

    public function create($request)
    {
        $item = new Item($request);
        $item->created_by = Auth::user()->id;
        $item->save();
        $this->createSaleActivity($item->id, 6, ActivityKey::CREATE_ITEM);
        if (isset($request['customFieldValue'])) {
            foreach ($request['customFieldValue'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $item->id;
                $customFields->field_to = "items";
                $customFields->save();
            }
        }
        $data = Item::where('id', $item->id)->with(
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->first();
        return $data;
    }

    public function update($id, $request)
    {
        $item = Item::find($id);

        if (!$item) {
            return null;
        }

        $item->fill($request);
        $item->updated_by = Auth::user()->id;
        $item->save();
        $this->createSaleActivity($id, 6, ActivityKey::UPDATE_ITEM);
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cValues) {
                $cfvId = isset($cValues['id']) ? $cValues['id'] : 0;
                $customFieldsValues = CustomFieldValue::findorNew($cfvId);
                $customFieldsValues->fill($cValues);
                $customFieldsValues->rel_id = $item->id;
                $customFieldsValues->field_to = "items";
                $customFieldsValues->save();
                $customFields[] = $customFieldsValues->id;
            }
            CustomFieldValue::where('rel_id', $item->id)->whereNotIn('id', $customFields)->delete();
        }
        $data = Item::where('id', $item->id)->with(
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->get();
        return $data;
    }

    public function destroy($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return null;
        }
        $this->createSaleActivity($id, 6, ActivityKey::DELETE_ITEM);
        $item->delete();
        return $item;
    }
}
