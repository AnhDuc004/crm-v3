<?php

namespace Modules\Project\Repositories\ProjectDiscussions;

use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Illuminate\Support\Facades\Auth;
use Modules\Project\Entities\ProjectDiscussions;

class ProjectDiscussionsRepository implements ProjectDiscussionsInterface
{
    use LogActivityTrait;

    public function listByProject($project_id, $queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $search = isset($queryData["search"]) ? $queryData["search"] : null;
        $baseQuery  = ProjectDiscussions::join('projects', 'project_discussions.project_id', '=', 'projects.id')
            ->where('projects.id', $project_id)->with('project', 'staff', 'contact')->select('project_discussions.*');
        if ($search) {
            $baseQuery = $baseQuery->where('subject', 'like', '%' . $search . '%');
        }
        $project_discussion = $limit > 0 ? $baseQuery->paginate($limit) : $baseQuery->get();
        if ($project_discussion->isEmpty()) {
            return null;
        }
        $data = [];
        foreach ($project_discussion as $discussion) {
            $data[] = [
                'id' => $discussion->id,
                'project_id' => $discussion->project_id,
                'subject' => $discussion->subject,
                'description' => $discussion->description,
                'show_to_customer' => $discussion->show_to_customer,
                'staff_id' => $discussion->staff_id,
                'contact_id' => $discussion->contact_id,
                'created_by' => $discussion->created_by,
                'updated_by' => $discussion->updated_by,
            ];
        }
        return $data;
    }

    public function create($project_id, $requestData)
    {
        $project_discussion =  new ProjectDiscussions($requestData);
        $project_discussion->project_id = $project_id;
        $project_discussion->updated_by = Auth::id();
        if (!$project_discussion->created_by) {
            $project_discussion->created_by = Auth::id();
        }
        $project_discussion->save();
        $this->createProjectActivity($project_id, ActivityKey::CREATE_DISCUSSIONS_BY_PROJECT);
        $data = [
            'id' => $project_discussion->id,
            'project_id' => $project_discussion->project_id,
            'description' => $project_discussion->description,
            'subject' => $project_discussion->subject,
            'staff_id' => $project_discussion->staff_id,
            'contact_id' => $project_discussion->contact_id,
            'show_to_customer' => $project_discussion->show_to_customer,
            'created_by' => $project_discussion->created_by,
            'updated_by' => $project_discussion->updated_by,
        ];
        return $data;
    }
    public function update($id, $requestData)
    {
        $project_discussion = ProjectDiscussions::find($id);
        if (!$project_discussion) {
            return null;
        }
        $project_discussion->fill($requestData);
        $project_discussion->updated_by = Auth::id();
        if (!$project_discussion->created_by) {
            $project_discussion->created_by = Auth::id();
        }
        $project_discussion->save();
        $this->createProjectActivity($project_discussion->project_id, ActivityKey::UPDATE_DISCUSSIONS_BY_PROJECT);
        return $project_discussion;
    }

    public function destroy($id)
    {
        $project_discussion = ProjectDiscussions::find($id);
        if (!$project_discussion) {
            return null;
        }
        $this->createProjectActivity($project_discussion->project_id, ActivityKey::DELETE_DISCUSSIONS_BY_PROJECT);
        $project_discussion->delete();
        return $project_discussion;
    }
}
