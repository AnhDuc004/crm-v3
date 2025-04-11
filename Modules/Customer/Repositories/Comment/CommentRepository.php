<?php

namespace Modules\Customer\Repositories\Comment;
use Modules\Sale\Entities\ProposalComments;

class CommentRepository implements CommentInterface
{
    public function findId($id)
    {
        $comments = ProposalComments::find($id);
        if (!$comments) {
            return null;
        }
        return $comments;
    }

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;

        $baseQuery = ProposalComments::query();

        $comments = $baseQuery->orderBy('created_at', 'desc');

        if ($limit > 0) {
            $comments = $baseQuery->paginate($limit);
        } else {
            $comments = $baseQuery->get();
        }
        return $comments;
    }

    public function create($requestData)
    {
        $comments =  new ProposalComments($requestData);
        $comments->save();
        return $comments;
    }

    public function update($id, $requestData)
    {
        $comments = ProposalComments::find($id);
        if (!$comments) {
            return null;
        }
        $comments->fill($requestData);
        $comments->save();
        return $comments;
    }

    public function destroy($id)
    {
        $comments = ProposalComments::find($id);
        if (!$comments) {
            return null;
        }
        $comments->delete();
        return $comments;
    }
}
