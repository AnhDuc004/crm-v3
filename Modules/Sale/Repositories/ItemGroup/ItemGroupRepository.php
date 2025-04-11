<?php

namespace Modules\Sale\Repositories\ItemGroup;
use Modules\Sale\Entities\ItemGroup;

class ItemGroupRepository implements ItemGroupInterface
{
    public function findId($id)
    {
        $itemGroup = ItemGroup::find($id);
        if (!$itemGroup) {
            return null;
        }
        return $itemGroup;
    }

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $startDate = isset($queryData['startDate']) ? $queryData['startDate'] : null;
        $endDate = isset($queryData['endDate']) ? $queryData['endDate'] : null;
        $orderName = isset($queryData['orderName']) ? $queryData['orderName'] : 'id';
        $orderType = isset($queryData['orderType']) ? $queryData['orderType'] : 'desc';

        $baseQuery = ItemGroup::query();

        if (!empty($startDate) && !empty($endDate)) {
            $baseQuery = $baseQuery->whereBetween('dateadded', [$startDate, $endDate]);
        }

        if (!empty($startDate) && empty($endDate)) {
            $baseQuery = $baseQuery->whereDate('dateadded', '>=', $startDate);
        }

        if (empty($startDate) && !empty($endDate)) {
            $baseQuery = $baseQuery->whereDate('dateadded', '<=', $endDate);
        }

        $itemGroup = $baseQuery->orderBy($orderName, $orderType);

        if ($limit > 0) {
            $itemGroup = $baseQuery->paginate($limit);
        } else {
            $itemGroup = $baseQuery->get();
        }

        return $itemGroup;
    }

    public function listSelect() {}

    public function create($requestData)
    {
        $itemGroup = new ItemGroup($requestData);
        $itemGroup->save();

        $data = ItemGroup::where('id', $itemGroup->id)->get();
        return $data;
    }

    public function update($id, $requestData)
    {
        $itemGroup = ItemGroup::find($id);
        if (!$itemGroup) {
            return null;
        }

        $itemGroup->fill($requestData);
        $itemGroup->save();

        $data = ItemGroup::where('id', $itemGroup->id)->get();
        return $data;
    }

    public function destroy($id)
    {
        $itemGroup = ItemGroup::find($id);
        if (!$itemGroup) {
            return null;
        }
        $itemGroup->delete();
        return $itemGroup;
    }
}
