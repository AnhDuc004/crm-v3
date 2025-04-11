<?php

namespace Modules\Support\Repositories\SpamFilter;

use Modules\Support\Entities\SpamFilter;

class SpamFilterRepository implements SpamFilterInterface
{
    public function findId($id)
    {
        $spamFilter = SpamFilter::find($id);
        if (!$spamFilter) {
            return null;
        }
        return $spamFilter;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $typeTicket = isset($request["typeTicket"]) ? $request["typeTicket"] : null;
        $typeLead = isset($request["typeLead"]) ? $request["typeLead"] : null;
        $baseQuery = SpamFilter::query();
        if ($search) {
            $baseQuery = $baseQuery->where('value', 'like',  '%' . $search . '%');
        }
        if ($typeTicket) {
            $baseQuery = $baseQuery->where('type', $typeTicket)->where('rel_type', '=', 'tickets');
        }

        if ($typeLead) {
            $baseQuery = $baseQuery->where('type', $typeLead)->where('rel_type', '=', 'leads');
        }
        $spamFilter = $baseQuery->orderBy('id', 'desc');
        if ($limit > 0) {
            $spamFilter = $baseQuery->paginate($limit);
        } else {
            $spamFilter = $baseQuery->get();
        }

        return $spamFilter;
    }

    public function create($request)
    {
        $spamFilter = new SpamFilter($request);
        $spamFilter->rel_type = "tickets";
        $spamFilter->save();
        return $spamFilter;
    }

    public function update($id, $request)
    {
        $spamFilter = SpamFilter::find($id);
        if (!$spamFilter) {
            return null;
        }
        $spamFilter->fill($request);
        $spamFilter->save();
        return $spamFilter;
    }

    public function destroy($id)
    {
        $spamFilter = SpamFilter::find($id);
        if (!$spamFilter) {
            return null;
        }
        $spamFilter->delete();
        return $spamFilter;
    }

    public function createByLead($request)
    {
        $spamFilter = new SpamFilter($request);
        $spamFilter->rel_type = "leads";
        $spamFilter->save();
        return $spamFilter;
    }
}
