<?php

namespace Modules\Project\Repositories\ProjectMilestone;
use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Carbon\Carbon;
use Modules\Project\Entities\ProjectMilestone;

class ProjectMilestoneRepository implements ProjectMilestoneInterface
{
    use LogActivityTrait;

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;

        $baseQuery = projectMilestone::query();

        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like', '%' . $search . '%');
        }

        $projectMilestone = $baseQuery->with('project')->orderBy('id', 'desc');
        if ($limit > 0) {
            $projectMilestone = $baseQuery->paginate($limit);
        } else {
            $projectMilestone = $baseQuery->get();
        }

        return $projectMilestone;
    }
    public function create($id, $request)
    {
        $project_milestone =  new ProjectMilestone($request);
        $project_milestone->project_id = $id;
        $project_milestone->datecreated = Carbon::now();
        $project_milestone->save();
        $this->createProjectActivity($id, ActivityKey::CREATE_MILESTONE_BY_PROJECT);
        $data = ProjectMilestone::where('id', $project_milestone->id)->with('project')->first();
        return $data;
    }

    public function destroy($id)
    {
        $projectMilestone = ProjectMilestone::find($id);
        if (!$projectMilestone) {
            return null;
        }
        $this->createProjectActivity($projectMilestone->project_id, ActivityKey::DELETE_MILESTONE_BY_PROJECT);
        $result = $projectMilestone->delete();
        return $result;
    }
    public function update($id, $request)
    {
        $projectMilestone = ProjectMilestone::find($id);
        if (!$projectMilestone) {
            return null;
        }

        $projectMilestone->fill($request);
        $projectMilestone->save();
        $this->createProjectActivity($projectMilestone->project_id, ActivityKey::UPDATE_MILESTONE_BY_PROJECT);
        return $projectMilestone;
    }
    public function listByProject($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery  = ProjectMilestone::where('project_id', $id);
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like', '%' . $search . '%')
                ->orWhere('due_date', 'like', '%' . $search . '%');
        }
        if ($limit > 0) {
            $project_milestone = $baseQuery->paginate($limit);
        } else {
            $project_milestone = $baseQuery->get();
        }
        return $project_milestone;
    }
}
