<?php

namespace Modules\Customer\Repositories\CustomField;

use Illuminate\Support\Facades\Auth;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Repositories\CustomField\CustomFieldInterface;

class CustomFieldRepository implements CustomFieldInterface
{

    public function findId($id)
    {
        $customField = CustomField::find($id);
        return $customField;
    }

    public function getByName($id)
    {
        $customField = CustomField::where('field_to', $id)->get();
        return $customField;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = CustomField::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%')
                ->orWhere('field_to', 'like',  '%' . $search . '%')
                ->orWhere('type', 'like',  '%' . $search . '%')
                ->orWhere('slug', 'like',  '%' . $search . '%');
        }
        if ($limit > 0) {
            $customField = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $customField = $baseQuery->get();
        }

        return $customField;
    }

    public function listSelect() {}

    public function create($request)
    {
        $customField = new CustomField($request);
        if (isset($request['options'])) {
            $options = explode(',', $request['options']);
            $customField->options = collect($options);
            $customField->created_by = Auth::id();
        }
        $customField->save();
        return $customField;
    }

    public function update($id, $request)
    {
        $customField = CustomField::find($id);
        $customField->fill($request);
        $customField->save();
        return $customField;
    }

    public function destroy($id)
    {
        $customField = CustomField::find($id);
        $customField->delete();
        return $customField;
    }

    // Thay đổi trạng thái của customField
    public function toggleActive($id)
    {
        $customField = CustomField::find($id);
        $customField->active = !$customField->active;
        $customField->save();
        return $customField;
    }
}
