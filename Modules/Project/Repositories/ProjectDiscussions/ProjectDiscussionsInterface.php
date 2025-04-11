<?php

namespace Modules\Project\Repositories\ProjectDiscussions;

interface ProjectDiscussionsInterface
{
    public function listByProject($requestData, $project_id);

    public function create($requestData, $project_id);

    public function update($id, $requestData);

    public function destroy($id);

}
