<?php

namespace Modules\Lead\Repositories\WebToLead;

use Modules\Lead\Entities\WebToLead;

class WebToLeadRepository implements WebToLeadInterface
{
    public function findId($id)
    {
        $WebToLead = WebToLead::find($id);
        if (!$WebToLead) {
            return null;
        }
        return $WebToLead;
    }

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $name = isset($queryData["name"]) ? $queryData["name"] : null;
        $order_name = isset($queryData["order_name"]) ? $queryData["order_name"] : 'id';
        $order_type = isset($queryData["order_type"]) ? $queryData["order_type"] : 'desc';

        $baseQuery = WebToLead::query();

        if ($name) {
            $baseQuery = $baseQuery->where('name', 'like', '%' . $name . '%');
        }

        $webToLead = $baseQuery->orderBy($order_name, $order_type);

        if ($limit > 0) {
            $webToLead = $baseQuery->paginate($limit);
        } else {
            $webToLead = $baseQuery->get();
        }

        return $webToLead;
    }

    public function create($requestData)
    {
        $webToLead =  new WebToLead($requestData);
        $webToLead->dateadded = date('Y-m-d H:i:s');
        $webToLead->save();
        return $webToLead;
    }

    public function update($id, $requestData)
    {
        $webToLead = WebToLead::find($id);
        if (!$webToLead) {
            return null;
        }
        $webToLead->fill($requestData);
        $webToLead->save();
        return $webToLead;
    }

    public function destroy($id)
    {
        $webToLead = WebToLead::find($id);
        if (!$webToLead) {
            return null;
        }
        $webToLead->delete();
        return $webToLead;
    }
}
