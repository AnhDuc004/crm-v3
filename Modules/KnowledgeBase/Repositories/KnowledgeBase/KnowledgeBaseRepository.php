<?php

namespace Modules\KnowledgeBase\Repositories\KnowledgeBase;

use Modules\KnowledgeBase\Entities\KnowledgeBase;

class KnowledgeBaseRepository implements KnowledgeBaseInterface
{

    public function findId($id)
    {
        $knowledgeBase = KnowledgeBase::find($id);
        if (!$knowledgeBase) {
            return null;
        }
        return $knowledgeBase;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $group = isset($request["group"]) ? json_decode($request["group"]) : null;
        $baseQuery = KnowledgeBase::leftJoin('knowledge_base_groups', 'knowledge_base_groups.groupid', '=', 'knowledge_base.articlegroup');
        if ($search) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($search) {
                    $q->where('knowledge_base.subject', 'like',  '%' . $search . '%')
                        ->orWhere('knowledge_base_groups.name', 'like',  '%' . $search . '%');
                }
            );
        }
        if ($group) {
            $baseQuery = $baseQuery->whereIn('knowledge_base_groups.groupid', $group);
        }
        $knowledgeBase = $baseQuery->with('group')->orderBy('knowledge_base.articleid', 'desc');

        if ($limit > 0) {
            $knowledgeBase = $baseQuery->paginate($limit);
        } else {
            $knowledgeBase = $baseQuery->get();
        }

        return $knowledgeBase;
    }

    public function create($request)
    {
        $knowledgeBase = new KnowledgeBase($request);
        $result = $knowledgeBase->save();
        if (!$result) {
            return null;
        }
        return $knowledgeBase;
    }

    public function update($id, $request)
    {
        $knowledgeBase = KnowledgeBase::find($id);
        if (!$knowledgeBase) {
            return null;
        }
        $knowledgeBase->fill($request);
        $result = $knowledgeBase->save();
        if (!$result) {
            return null;
        }
        return $knowledgeBase;
    }

    public function destroy($id)
    {
        $knowledgeBase = knowledgeBase::find($id);
        if (!$knowledgeBase) {
            return null;
        }
        $knowledgeBase->delete();
        return $knowledgeBase;
    }
}
