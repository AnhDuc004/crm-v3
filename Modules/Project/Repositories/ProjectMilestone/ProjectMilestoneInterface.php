<?php

namespace Modules\Project\Repositories\ProjectMilestone;

interface ProjectMilestoneInterface
{

    public function listAll($request);

    public function create($request, $id);

    public function destroy($id);

    public function listByProject($id, $request);

    public function update($id, $request);

}
