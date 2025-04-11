<?php

namespace Modules\Sale\Repositories\ProposalComment;
use Illuminate\Support\Facades\Auth;
use Modules\Sale\Entities\ProposalComments;

class ProposalCommentRepository implements ProposalCommentInterface
{
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $baseQuery = ProposalComments::query();
        if ($limit > 0) {
            $proposalComment = $baseQuery->paginate($limit);
        } else {
            $proposalComment = $baseQuery->get();
        }
        return $proposalComment;
    }

    // Thêm mới proposal comment
    public function create($id, $request)
    {
        $proposalComment = new ProposalComments($request);
        $proposalComment->staff_id = Auth::id();
        $proposalComment->proposal_id = $id;
        $proposalComment->created_by = Auth::id();
        $proposalComment->save();
        return $proposalComment;
    }

    // Cập nhật proposal comment
    public function update($id, $request)
    {
        $proposalComment = ProposalComments::find($id);
        $proposalComment->fill($request);
        $proposalComment->updated_by = Auth::id();
        $proposalComment->save();
        return $proposalComment;
    }

    // Xóa proposal comment
    public function destroy($id)
    {
        $proposalComment = ProposalComments::find($id);
        $proposalComment->delete();
        return $proposalComment;
    }
}
