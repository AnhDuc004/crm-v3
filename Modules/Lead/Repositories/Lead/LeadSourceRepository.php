<?php

namespace Modules\Lead\Repositories\Lead;
use Illuminate\Support\Facades\Auth;
use Modules\Lead\Entities\LeadSource;

class LeadSourceRepository implements LeadSourceInterface
{
    public function findId($id)
    {
        $lead = LeadSource::find($id);
        return $lead;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = LeadSource::query()->leftJoin('leads', 'leads.source', '=', 'leads_sources.id')
            ->select('leads_sources.id', 'leads_sources.name')
            ->selectRaw('COUNT(leads.source) as leadCount')
            ->groupBy('leads_sources.id', 'leads_sources.name');
        if ($search) {
            $baseQuery = $baseQuery->where('leads_sources.name', 'like',  '%' . $search . '%');
        }
        $lead = $baseQuery->orderBy('leads_sources.id', 'desc');

        if ($limit > 0) {
            $lead = $baseQuery->paginate($limit);
        } else {
            $lead = $baseQuery->get();
        }
        return $lead;
    }

    public function listSelect()
    {
        $leads =  LeadSource::orderBy('name')->get();
        return $leads;
    }

    public function create($request)
    {
        $lead = new LeadSource($request);
        $lead->created_by = Auth::id();
        $lead->save();
        return $lead;
    }

    public function update($id, $request)
    {
        $lead = LeadSource::find($id);
        $lead->fill($request);
        $lead->updated_by = Auth::id();
        $lead->save();
        return $lead;
    }

    public function destroy($id)
    {
        $lead = LeadSource::find($id);
        $lead->delete();
        return $lead;
    }
}
