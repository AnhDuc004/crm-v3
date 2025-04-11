<?php

namespace Modules\Project\Repositories\ProjectActivity;
use Modules\Project\Entities\ProjectActivity;

class ProjectActivityRepository implements ProjectActivityInterface
{
    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $baseQuery = ProjectActivity::query();
        $projectActivity = $baseQuery->with('project', 'contact', 'staff')->orderBy('id', 'desc');
        if ($limit > 0) {
            $projectActivity = $baseQuery->paginate($limit);
        } else {
            $projectActivity = $baseQuery->get();
        }

        return $projectActivity;
    }

    public function getListByProject($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $baseQuery = ProjectActivity::where('project_id', $id)->limit(3);

        if (!$baseQuery) {
            return null;
        }
        $projectActivity = $baseQuery->orderBy('dateadded', 'desc');
        if ($limit > 0) {
            $projectActivity = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $projectActivity = $baseQuery->get();
        }

        return $projectActivity;
    }

    public function destroy($id)
    {
        $project = ProjectActivity::find($id);
        if (!$project) {
            return null;
        }
        $project->delete();
        return $project;
    }
}
