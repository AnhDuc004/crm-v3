<?php

namespace Modules\Customer\Repositories\Customer;

use Modules\Customer\Entities\Group;
use Modules\Customer\Repositories\Customer\CustomerGroupInterface;

class CustomerGroupRepository implements CustomerGroupInterface
{
    public function findId($id)
    {
        $group = Group::find($id);
        if (!$group) {
            return null;
        }
        return $group;
    }

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $search = isset($queryData["search"]) ? $queryData["search"] : null;
        $baseQuery = Group::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }
        $group = $baseQuery->orderBy('name', 'desc');

        if ($limit > 0) {
            $group = $baseQuery->paginate($limit);
        } else {
            $group = $baseQuery->get();
        }

        return $group;
    }

    public function listSelect()
    {
        $groups =  Group::orderBy('name')->get();
        return $groups;
    }

    public function create($requestData)
    {
        $group =  new Group($requestData);
        $result =  $group->save();
        if (!$result) {
            return null;
        }
        return $group;
    }

    public function update($id, $requestData)
    {
        $group = Group::find($id);
        if (!$group) {
            return null;
        }
        $group->fill($requestData);
        $result =  $group->save();
        if (!$result) {
            return null;
        }
        return $group;
    }

    public function destroy($id)
    {
        $group = Group::find($id);
        if (!$group) {
            return null;
        }
        $group->delete();
        return $group;
    }
}
