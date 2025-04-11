<?php

namespace Modules\Sale\Repositories\ProposalComment;

interface ProposalCommentInterface
{  
    public function listAll($request);

    public function create($id, $request);

    public function update($id, $request);

    public function destroy($id);

}