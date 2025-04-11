<?php

namespace Modules\Project\Repositories\ProjectNotes;

use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Illuminate\Support\Facades\Auth;
use Modules\Project\Entities\ProjectNotes;

class ProjectNotesRepository implements ProjectNotesInterface
{
    use LogActivityTrait;

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $baseQuery = ProjectNotes::query();
        $projectNotes = $baseQuery->with('project', 'staff')->orderBy('id', 'desc');
        if ($limit > 0) {
            $projectNotes = $baseQuery->paginate($limit);
        } else {
            $projectNotes = $baseQuery->get();
        }

        return $projectNotes;
    }
    public function create($id, $request)
    {
        $project_notes = new ProjectNotes($request);
        $project_notes->project_id = $id;
        $project_notes->staff_id = Auth::user()->id;
        $project_notes->save();
        $this->createProjectActivity($id, ActivityKey::CREATE_NOTE_BY_PROJECT);
        $data = ProjectNotes::where('id', $project_notes->id)->with('project')->first();
        return $data;
    }

    public function destroy($id)
    {
        $projectNotes = ProjectNotes::find($id);
        if (!$projectNotes) {
            return null;
        }
        $this->createProjectActivity($projectNotes->project_id, ActivityKey::DELETE_NOTE_BY_PROJECT);
        $result = $projectNotes->delete();
        return $result;
    }
    public function update($id, $request)
    {
        $projectNotes = ProjectNotes::find($id);
        if (!$projectNotes) {
            return null;
        }
        $projectNotes->fill($request);
        $projectNotes->save();
        $this->createProjectActivity($projectNotes->project_id, ActivityKey::UPDATE_NOTE_BY_PROJECT);
        return $projectNotes;
    }

    public function getListByProject($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $baseQuery = ProjectNotes::query()->where('project_id', $id);
        $projectNotes = $baseQuery->with('project:id,name', 'staff:id,first_name,last_name')->orderBy('id', 'desc');
        if ($limit > 0) {
            $projectNotes = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $projectNotes = $baseQuery->get();
        }
        return $projectNotes;
    }
}
