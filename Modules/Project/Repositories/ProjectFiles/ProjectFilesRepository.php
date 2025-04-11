<?php

namespace Modules\Project\Repositories\ProjectFiles;

use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Modules\Project\Entities\ProjectFiles;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProjectFilesRepository implements ProjectFilesInterface
{
    use LogActivityTrait;

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $baseQuery = ProjectFiles::query();

        if ($limit > 0) {
            $projectFiles = $baseQuery->paginate($limit);
        } else {
            $projectFiles = $baseQuery->get();
        }
        return $projectFiles;
    }
    public function create($id, $requestData)
    {
        $project_file =  new ProjectFiles($requestData);
        $project_file->project_id = $id;
        $project_file->dateadded = Carbon::now();
        $project_file->save();
        $data = ProjectFiles::where('id', $project_file->id)->with('project', 'contact')->get();
        return $data;
    }

    public function destroy($id)
    {
        $projectFiles = ProjectFiles::find($id);
        if (!$projectFiles) {
            return null;
        }
        $this->createProjectActivity($projectFiles->project_id, ActivityKey::DELETE_FILE_BY_PROJECT);
        $result = $projectFiles->delete();
        return $result;
    }

    public function update($id, $requestData)
    {
        $projectFiles = ProjectFiles::find($id);
        if (!$projectFiles) {
            return null;
        }
        $projectFiles->fill($requestData);
        $projectFiles->save();
        $this->createProjectActivity($projectFiles->project_id, ActivityKey::UPDATE_FILE_BY_PROJECT);
        return $projectFiles;
    }

    public function uploadFileByProject($id, $request)
    {
        $file = new ProjectFiles($request);
        $file->project_id = $id;
        $file->staff_id = Auth::id();
        $file->updated_by = Auth::id();
        if (!$file->created_by) {
            $file->created_by = Auth::id();
        }
        $fileUpLoad = isset($request['file_name']) ? $request['file_name'] : null;
        if ($fileUpLoad) {
            $fileName = $fileUpLoad->getClientOriginalName();
            $fileType = $fileUpLoad->getMimeType();
            $fileUpLoad->move(storage_path('app/public/uploads/'), $fileName);
            $file->file_name = $fileName;
            $file->file_type = $fileType;
        };
        $contactEmail = $request['contact_email'] ?? null;
        if ($contactEmail) {
            $contact = DB::table('contacts')->where('email', $contactEmail)->first();
            $file->contact_id = $contact ? $contact->id : 0;
        } else {
            $file->contact_id = 0;
        }
        $file->subject = isset($request['subject']) ? $request['subject'] : null;
        $file->description = isset($request['description']) ? $request['description'] : null;
        $file->last_activity = isset($request['last_activity']) ? $request['last_activity'] : now();
        $file->visible_to_customer = isset($request['visible_to_customer']) ? ($request['visible_to_customer'] ? 1 : 0) : 1;
        $file->external = isset($request['external']) ? $request['external'] : null;
        $file->external_link = isset($request['external_link']) ? $request['external_link'] : null;
        $file->thumbnail_link = isset($request['thumbnail_link']) ? $request['thumbnail_link'] : null;
        $file->save();
        $this->createProjectActivity($id, key: ActivityKey::CREATE_FILE_BY_PROJECT);
        $data = [
            'id' => $file->id,
            'file_name' => $file->file_name,
            'subject' => $file->subject,
            'description' => $file->description,
            'file_type' => $file->file_type,
            'last_activity' => $file->last_activity,
            'project_id' => $file->project_id,
            'visible_to_customer' => $file->visible_to_customer,
            'staff_id' => $file->staff_id,
            'contact_id' => $file->contact_id,
            'external' => $file->external,
            'external_link' => $file->external_link,
            'thumbnail_link' => $file->thumbnail_link,
            'created_by' => $file->created_by,
            'updated_by' => $file->updated_by,
        ];
        return $data;
    }

    public function getListByProject($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 10;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request['search'] : null;
        $baseQuery = ProjectFiles::where('project_id', '=', $id)->where('staff_id', Auth::id());
        if (!$baseQuery) {
            return null;
        }
        if ($search) {
            $baseQuery = $baseQuery->where('project_files.file_name', 'like',  '%' . $search . '%');
        }
        $file = $baseQuery->with('staff:id,first_name,last_name')->select('project_files.*');
        if ($limit > 0) {
            $file = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $file = $baseQuery->get();
        }
        return $file;
    }

    public function changeVisibleToCustomer($id, $request)
    {
        $visible = $request['visible_to_customer'];
        $file = ProjectFiles::find($id);
        $file->visible_to_customer = $visible;
        $file->save();
        $this->createProjectActivity($file->project_id, ActivityKey::CHANGE_VISIBLE_TO_CUSTOMER_BY_FILE_BY_PROJECT);
        return $file;
    }
}
