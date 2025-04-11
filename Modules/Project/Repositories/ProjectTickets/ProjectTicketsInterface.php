<?php

namespace Modules\Project\Repositories\ProjectTickets;

interface ProjectTicketsInterface
{

    public function listAll($request);

    public function create($request, $project_id);

    public function destroy($id);

    public function listByProject($id, $request);

    public function update($id,$request);

    public function countByStatus($id);

}
