<?php

namespace Modules\Lead\Repositories\Lead;

use Exception;
use App\Helpers\Result;
use Modules\Lead\Entities\LeadStatus;
use Modules\Lead\Entities\Lead;
use Illuminate\Support\Facades\Auth;

class LeadStatusRepository implements LeadStatusInterface
{

    public function findId($id)
    {
        $lead = LeadStatus::find($id);
        return $lead;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = LeadStatus::query()->leftJoin('leads', 'leads.status', '=', 'leads_status.id')
            ->select('leads_status.id', 'leads_status.name')
            ->selectRaw('COUNT(leads.status) as leadCount')
            ->groupBy('leads_status.id', 'leads_status.name');
        if ($search) {
            $baseQuery = $baseQuery->where('leads_status.name', 'like',  '%' . $search . '%');
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
        $leads =  LeadStatus::orderBy('name')->get();
        return $leads;
    }

    public function create($request)
    {
        $lead = new LeadStatus($request);
        $lead->created_by = Auth::id();
        $lead->save();
        return $lead;
    }

    public function update($id, $request)
    {
        $lead = LeadStatus::find($id);
        $lead->fill($request);
        $lead->updated_by = Auth::id();
        $lead->save();
        return $lead;
    }

    public function destroy($id)
    {
        $lead = LeadStatus::find($id);
        $lead->delete();
        return $lead;
    }
}
