<?php

namespace Modules\KnowledgeBase\Repositories\KnowledgeBaseGroup;

use Modules\KnowledgeBase\Entities\KnowledgeBaseGroup;

class KnowledgeBaseGroupRepository implements KnowledgeBaseGroupInterface
{
    public function findId($id)
    {
        $knowledgeBaseGroup = KnowledgeBaseGroup::find($id);
        if (!$knowledgeBaseGroup) {
            return null;
        }
        return $knowledgeBaseGroup;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $name = isset($request["name"]) ? $request["name"] : null;
        $baseQuery = KnowledgeBaseGroup::query();
        $knowledgeBaseGroup = $baseQuery->orderBy('groupid', 'desc');

        if ($limit > 0) {
            $knowledgeBaseGroup = $baseQuery->paginate($limit);
        } else {
            $knowledgeBaseGroup = $baseQuery->get();
        }
        return $knowledgeBaseGroup;
    }

    public function create($request)
    {
        $knowledgeBaseGroup = new KnowledgeBaseGroup($request);
        $result = $knowledgeBaseGroup->save();
        if (!$result) {
            return null;
        }
        return $result;
    }

    public function update($id, $request)
    {
        $knowledgeBaseGroup = KnowledgeBaseGroup::find($id);
        if (!$knowledgeBaseGroup) {
            return null;
        }
        $knowledgeBaseGroup->fill($request);
        $result = $knowledgeBaseGroup->save();
        if (!$result) {
            return null;
        }
        return $knowledgeBaseGroup;
    }

    public function destroy($id)
    {
        $knowledgeBaseGroup = KnowledgeBaseGroup::find($id);
        if (!$knowledgeBaseGroup) {
            return null;
        }
        $knowledgeBaseGroup->delete();
        return $knowledgeBaseGroup;
    }
}
