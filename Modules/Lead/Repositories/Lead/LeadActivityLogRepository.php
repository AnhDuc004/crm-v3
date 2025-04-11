<?php

namespace Modules\Lead\Repositories\Lead;

use Modules\Lead\Entities\LeadActivityLog;

class LeadActivityLogRepository implements LeadActivityLogInterface
{
    public function findId($id)
    {
        $lead = LeadActivityLog::find($id);
        if (!$lead) {
            return null;
        }
        return $lead;
    }

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $search = isset($queryData["search"]) ? $queryData["search"] : null;
        $startDate = isset($queryData['startDate']) ? $queryData['startDate'] : null;
        $endDate = isset($queryData['endDate']) ? $queryData['endDate'] : null;
        $orderName = isset($queryData['orderName']) ? $queryData['orderName'] : 'id';
        $orderType = isset($queryData['orderType']) ? $queryData['orderType'] : 'asc';

        $baseQuery = LeadActivityLog::query();
        if ($search) {
            $baseQuery = $baseQuery->where('full_name', 'like', '%' . $search . '%');
        }


        if (!empty($startDate) && !empty($endDate)) {
            $baseQuery = $baseQuery->whereBetween('date', [$startDate, $endDate]);
        }

        if (!empty($startDate) && empty($endDate)) {
            $baseQuery = $baseQuery->whereDate('date', '>=', $startDate);
        }

        if (empty($startDate) && !empty($endDate)) {
            $baseQuery = $baseQuery->whereDate('date', '<=', $endDate);
        }

        $lead = $baseQuery->orderBy($orderName, $orderType);
        if ($limit > 0) {
            $lead = $baseQuery->paginate($limit);
        } else {
            $lead = $baseQuery->get();
        }

        return $lead;
    }

    public function listSelect()
    {
        $leads =  LeadActivityLog::orderBy('name')->get();
        return $leads;
    }

    public function create($requestData)
    {
        $lead = new LeadActivityLog($requestData);
        $lead->date = date('Y-m-d H:i:s');
        $lead->save();
        return $lead;
    }

    public function update($id, $requestData)
    {
        $lead = LeadActivityLog::find($id);
        if (!$lead) {
            return null;
        }
        $lead->fill($requestData);
        $lead->save();
        return $lead;
    }

    public function destroy($id)
    {
        $lead = LeadActivityLog::find($id);
        if (!$lead) {
            return null;
        }
        $lead->delete();
        return $lead;
    }

    public function getListByLead($id, $queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $baseQuery = LeadActivityLog::where('leadid', $id)->limit(3);
        $lead = $baseQuery->orderBy('created_at', 'desc');
        if ($limit > 0) {
            $lead = $baseQuery->paginate($limit);
        } else {
            $lead = $baseQuery->get();
        }
        return $lead;
    }
}
