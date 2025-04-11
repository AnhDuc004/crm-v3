<?php

namespace Modules\Customer\Repositories\SpamFilter;

use Modules\Customer\Entities\SpamFilter;

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

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $baseQuery = SpamFilter::query();

        if ($limit > 0) {
            $spamFilter = $baseQuery->paginate($limit);
        } else {
            $spamFilter = $baseQuery->get();
        }

        return $spamFilter;
    }

    public function listSelect() {}

    public function create($requestData)
    {
        $spamFilter = new SpamFilter($requestData);
        $spamFilter->save();
        return $spamFilter;
    }

    public function update($id, $requestData)
    {
        $spamFilter = SpamFilter::find($id);
        if (!$spamFilter) {
            return null;
        }
        $spamFilter->fill($requestData);
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
}
