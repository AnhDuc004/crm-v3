<?php

namespace Modules\Lead\Repositories\Lead;
use Modules\Lead\Entities\LeadIntegrationEmail;

class LeadIntegrationEmailRepository implements LeadIntegrationEmailInterface
{
    public function findId($id)
    {
        $lead = LeadIntegrationEmail::find($id);
        if (!$lead) {
            return null;
        }
        return $lead;
    }

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $search = isset($queryData["search"]) ? $queryData["search"] : null;
        $startDate = isset($queryData["startDate"]) ? $queryData["startDate"] : null;
        $endDate = isset($queryData["endDate"]) ? $queryData["endDate"] : null;

        $baseQuery = LeadIntegrationEmail::query();
        if ($search) {
            $baseQuery = $baseQuery->where('subject', 'like', '%' . $search . '%');
        }

        if (!empty($startDate) && !empty($endDate)) {
            $baseQuery = $baseQuery->whereBetween('dateadded', [$startDate, $endDate]);
        }

        if (!empty($startDate) && empty($endDate)) {
            $baseQuery = $baseQuery->whereDate('dateadded', '>=', $startDate);
        }

        if (empty($startDate) && !empty($endDate)) {
            $baseQuery = $baseQuery->whereDate('dateadded', '<=', $endDate);
        }

        $lead = $baseQuery->orderBy('id', 'desc');

        if ($limit > 0) {
            $lead = $baseQuery->paginate($limit);
        } else {
            $lead = $baseQuery->get();
        }
        return $lead;
    }

    public function listSelect()
    {
        $leads =  LeadIntegrationEmail::orderBy('name')->get();
        return $leads;
    }

    public function create($requestData)
    {
        $lead = new LeadIntegrationEmail($requestData);
        $lead->dateadded = date('Y-m-d H:i:s');
        $lead->save();
        return $lead;
    }

    public function update($id, $requestData)
    {
        $lead = LeadIntegrationEmail::find($id);
        if (!$lead) {
            return null;
        }
        $lead->fill($requestData);
        $lead->save();
        return $lead;
    }

    public function destroy($id)
    {
        $lead = LeadIntegrationEmail::find($id);
        if (!$lead) {
            return null;
        }
        $lead->delete();
        return $lead;
    }
}
