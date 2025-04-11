<?php

namespace Modules\Support\Repositories\PredefinedReplies;

use Modules\Support\Entities\PredefinedReplies;

class PredefinedRepliesRepository implements PredefinedRepliesInterface
{
    public function findId($id)
    {
        $predefinedReplies = PredefinedReplies::find($id);
        if (!$predefinedReplies) {
            return null;
        }
        return $predefinedReplies;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = PredefinedReplies::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }

        $predefinedReplies = $baseQuery->orderBy('id', 'desc');
        if ($limit > 0) {
            $predefinedReplies = $baseQuery->paginate($limit);
        } else {
            $predefinedReplies = $baseQuery->get();
        }

        return $predefinedReplies;
    }

    public function create($request)
    {
        $predefinedReplies = new PredefinedReplies($request);
        $predefinedReplies->save();
        return $predefinedReplies;
    }

    public function update($id, $request)
    {
        $predefinedReplies = PredefinedReplies::find($id);
        if (!$predefinedReplies) {
            return null;
        }
        $predefinedReplies->fill($request);
        $predefinedReplies->save();
        return $predefinedReplies;
    }

    public function destroy($id)
    {
        $predefinedReplies = PredefinedReplies::find($id);
        if (!$predefinedReplies) {
            return null;
        }
        $predefinedReplies->delete();
        return $predefinedReplies;
    }
}
