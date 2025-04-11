<?php

namespace Modules\Sale\Repositories\Itemable;
use Modules\Sale\Entities\Itemable;

class ItemableRepository implements ItemableInterface
{
    public function findId($id)
    {
        $itemable = Itemable::find($id);
        if (!$itemable) {
            return null;
        }
        return $itemable;
    }

    public function findInvoice($id, $queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $baseQuery = Itemable::join('invoices', 'invoices.id', '=', 'itemable.rel_id')
            ->where('itemable.rel_type', '=', 'invoice')
            ->where('invoices.clientId', '=', $id);
        if (!$baseQuery) {
            return null;
        }
        $itemable = $baseQuery->with('invoice')->select('itemable.*');
        if ($limit > 0) {
            $itemable = $baseQuery->paginate($limit);
        } else {
            $itemable = $baseQuery->get();
        }
        return $itemable;
    }

    public function listAll($requestData)
    {
        $limit = isset($requestData["limit"]) && ctype_digit($requestData["limit"]) ? (int) $requestData["limit"] : 0;
        $baseQuery = Itemable::query();
        if ($limit > 0) {
            $itemable = $baseQuery->paginate($limit);
        } else {
            $itemable = $baseQuery->get();
        }
        return $itemable;
    }

    public function listSelect() {}

    public function create($requestData)
    {
        $itemable = new Itemable($requestData);
        $itemable->save();
        return $itemable;
    }

    public function update($id, $requestData)
    {
        $itemable = Itemable::find($id);
        if (!$itemable) {
            return null;
        }
        $itemable->fill($requestData);
        $itemable->save();
        return $itemable;
    }

    public function destroy($id)
    {
        $itemable = Itemable::find($id);
        if (!$itemable) {
            return null;
        }
        $itemable->delete();
        return $itemable;
    }
}
