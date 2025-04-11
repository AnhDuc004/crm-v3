<?php

namespace Modules\Lead\Repositories\Lead;
use Modules\Lead\Entities\LeadEmailIntegration;

class LeadEmailIntegrationRepository implements LeadEmailIntegrationInterface
{
    public function findId($id)
    {
        $lead = LeadEmailIntegration::find($id);
        if (!$lead) {
            return null;
        }
        return $lead;
    }

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $search = isset($queryData["search"]) ? $queryData["search"] : null;

        $baseQuery = LeadEmailIntegration::query();
        if ($search) {
            $baseQuery = $baseQuery->where('email', 'like', '%' . $search . '%');
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
        $leads =  LeadEmailIntegration::orderBy('name')->get();
        return $leads;
    }

    public function create($requestData)
    {
        $lead = new LeadEmailIntegration($requestData);
        $lead->password = bcrypt($requestData['password']);
        $lead->save();
        return $lead;
    }

    public function update($id, $requestData)
    {
        $lead = LeadEmailIntegration::find($id);
        $data = $requestData;
        if (!$lead) {
            return null;
        }
        if (isset($requestData['password'])) {
            $data['password'] = bcrypt($requestData['password']);
        }
        $lead->update($data);
        return $lead;
    }

    public function destroy($id)
    {
        $lead = LeadEmailIntegration::find($id);
        if (!$lead) {
            return null;
        }
        $lead->delete();
        return $lead;
    }
}
