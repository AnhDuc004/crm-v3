<?php

namespace Modules\Task\Repositories\Task;

use App\Exports\TaskExport;
use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use App\Traits\NotificationsTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\File;
use Modules\Admin\Entities\Staff;
use Modules\Customer\Entities\Tag;
use Modules\Task\Entities\Task;
use Modules\Task\Entities\TaskAssign;
use Modules\Task\Entities\TaskChecklist;
use Modules\Task\Entities\TaskComment;
use Modules\Task\Entities\TaskFollower;
use Modules\Task\Entities\TaskTimer;
use Intervention\Image\Facades\Image;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Customer\Entities\Taggables;
use Excel;
use Illuminate\Support\Facades\Auth;
use App\Utils\Models\ModeHasRole;
use Illuminate\Http\UploadedFile;
use Modules\Customer\Entities\Customer;
use Modules\Lead\Entities\LeadActivityLog;
use Modules\Project\Entities\Project;
use Modules\Project\Entities\ProjectActivity;

class TaskRepository implements TaskInterface
{
    use LogActivityTrait;
    use NotificationsTrait;

    // Lấy task theo id
    public function findId($id)
    {
        $task = Task::where('id', $id)->with(
            'tags',
            'rel_contract',
            'comments',
            'checklist',
            'timer',
            'assigned',
            'follower',
            'reminder',
            'customFields:id,field_to,name',
            'customFieldsValues',
            'files'
        )->first();
        return $task;
    }

    // Danh sách task theo customer
    public function getListByCustomer($id, $request)
    {
        return $this->applyFilterByRelation($id, 'customer');
    }

    // Danh sách task theo expense
    public function getListByExpense($id, $request)
    {
        return $this->applyFilterByRelation($id, 'expense');
    }

    // Đểm task theo status
    public function count($request)
    {
        $staff_id = Auth::id();
        $statuses = [1 => 'notstarted', 2 => 'inprogress', 3 => 'testing', 4 => 'feedback', 5 => 'complete'];
        $data = [];
        foreach ($statuses as $status => $statusKey) {
            $data[$statusKey] = Task::where('status', $status)->count();
            $data['me' . $statusKey] = Task::join('task_assigned', 'tasks.id', '=', 'task_assigned.task_id')
                ->where('tasks.status', $status)
                ->where('task_assigned.staff_id', $staff_id)
                ->count();
        }
        return $data;
    }

    // Danh sách task
    public function listAll($request)
    {
        $staff_id = Staff::where('user_id', Auth::id())->first()->id;
        // Log::debug($staff_id);
        $role = Auth::user()->roles->pluck('id')->toArray();
        // Log::debug($role);
        $admin_roles = [7, 9, 10]; // Danh sách role có quyền xem tất cả task

        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $search = $request["search"] ?? null;
        $status = isset($request["status"]) ? json_decode($request["status"], true) : null;
        $tags = $request["tags"] ?? null;

        // Các tiêu chí lọc
        $today = $request["today"] ?? null;
        $dueDate = $request["dueDate"] ?? null;
        $upcoming = $request["upcoming"] ?? null;
        $assigned = isset($request["assigned"]) ? (int) $request["assigned"] : 0;
        $follower = isset($request["follower"]) ? (int) $request["follower"] : 0;
        $notAssigned = isset($request["notAssigned"]) ? (int) $request["notAssigned"] : 0;
        $recurring = $request["recurring"] ?? null;
        $billable = $request["billable"] ?? null;
        $billed = isset($request["billed"]) ? (int) $request["billed"] : 0;
        $member = isset($request["member"]) ? (int) $request["member"] : 0;
        $project_id = isset($request["project_id"]) ? (int) $request["project_id"] : 0;

        // Chuyển đổi ngày tháng nếu có
        if ($dueDate) $dueDate = Carbon::parse($dueDate)->toDateString();
        if ($upcoming) $upcoming = Carbon::parse($upcoming)->toDateString();
        if ($today) $today = Carbon::parse($today)->toDateString();

        // Base query
        $tasks = Task::query();

        if ($project_id) {
            $tasks->where('rel_id', '=', $project_id)->where('rel_type', '=', 'project');
        }

        // Kiểm tra quyền user
        if (!array_intersect($role, $admin_roles)) {
            $tasks->leftJoin('task_assigned', 'tasks.id', '=', 'task_assigned.task_id')
                ->leftJoin('task_followers', 'tasks.id', '=', 'task_followers.task_id')
                ->leftJoin('project_members', 'tasks.rel_id', '=', 'project_members.project_id')
                ->where(function ($query) use ($staff_id) {
                    $query->where('task_assigned.staff_id', $staff_id)
                        ->orWhere('tasks.created_by', $staff_id)
                        ->orWhere('task_followers.staff_id', $staff_id)
                        ->orWhere('project_members.staff_id', $staff_id);
                });
        }

        // Thêm các tiêu chí lọc chung
        if ($search) {
            $tasks->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhere('start_date', 'like', '%' . $search . '%')
                    ->orWhere('due_date', 'like', '%' . $search . '%');
            });
        }

        if ($tags) {
            $tasks->join('taggables', 'taggables.rel_id', '=', 'tasks.id')
                ->where('taggables.rel_type', 'task')
                ->join('tags', 'tags.id', '=', 'taggables.tag_id')
                ->where('tags.name', $tags);
        }

        if ($status) {
            $tasks->whereIn('tasks.status', $status);
        }

        // Lọc theo ngày tháng và các tiêu chí khác
        $tasks->when($today, fn($q) => $q->where("tasks.start_date", Carbon::now()->toDateString()))
            ->when($dueDate, fn($q) => $q->where("tasks.due_date", '=', $dueDate))
            ->when($upcoming, fn($q) => $q->where("tasks.start_date", '>', Carbon::now()->toDateString()))
            ->when($recurring, fn($q) => $q->where("tasks.recurring", $recurring))
            ->when($billable, fn($q) => $q->where("tasks.billable", $billable))
            ->when($billed, fn($q) => $q->where("tasks.billed", $billed));

        if ($member) {
            $tasks->join('task_assigned', 'task_assigned.task_id', '=', 'tasks.id')
                ->where('task_assigned.staff_id', $member);
        }
        if ($assigned) {
            $tasks->join('task_assigned', 'task_assigned.task_id', '=', 'tasks.id')
                ->where('task_assigned.staff_id', $assigned);
        }
        if ($follower) {
            $tasks->join('task_followers', 'task_followers.task_id', '=', 'tasks.id')
                ->where('task_followers.staff_id', $follower);
        }
        if ($notAssigned) {
            $tasks->leftJoin('task_assigned', 'task_assigned.task_id', '=', 'tasks.id')
                ->whereNull('task_assigned.task_id');
        }

        // Thêm quan hệ và sắp xếp
        $tasks = $this->applySelectRaw($tasks)->distinct()->select('tasks.*');

        return $this->applyPaginate($tasks, $limit, $page);
    }

    // Danh sách task theo project
    public function listByProject($project_id, $request)
    {
        return $this->applyFilterByRelation($project_id, 'project');
    }

    // Danh sách staff thep task, project
    public function listByStaff($project_id, $tasks_id)
    {
        $staff = Staff::query()->leftJoin('task_assigned', 'staff.id', '=', 'task_assigned.staff_id')
            ->leftJoin('tasks', 'tasks.id', '=', 'task_assigned.task_id')
            ->where('tasks.rel_id', $project_id)
            ->where('tasks.id', $tasks_id)
            ->where('tasks.rel_type', '=', 'project')
            ->select('staff.*')->distinct()
            ->get();
        return $staff;
    }

    //Danh sách task không nằm trong project
    public function listOutProject()
    {
        $tasks = Task::where('project_id', '=', '')->orWhereNull('project_id')->orderBy('created_at', 'desc')->with(['customers', 'users', 'taskNotes'])->get();
        return $tasks;
    }

    public function create($request)
    {
        $user = auth()->guard('api')->user();

        if (!$user) {
            return null;
        }

        // Kiểm tra quyền: hoặc có permission create task hoặc là admin
        $role = $user->roles->pluck('id')->toArray();
        $admin_roles = [7, 9, 10]; // Danh sách role admin

        if (!$user->hasPermissionTo('create task', 'web') && !array_intersect($role, $admin_roles)) {
            return null;
        }

        $staff_id = $user->id;

        // Tạo mới task
        $tasks = new Task($request);
        $tasks->created_by = $staff_id;
        $tasks->save();

        if ($tasks && $tasks->rel_type == 'project') {
            $this->createProjectActivity($tasks->rel_id, ActivityKey::CREATE_TASK_BY_PROJECT);
        } elseif ($tasks && $tasks->rel_type == 'lead') {
            $this->createLeadActivity($tasks->rel_id, ActivityKey::CREATE_TASK_BY_LEAD);
        }

        if (isset($request['file_name']) && is_array($request['file_name'])) {
            foreach ($request['file_name'] as $fileUpLoad) {
                if ($fileUpLoad instanceof UploadedFile) { // Kiểm tra đúng kiểu UploadedFile
                    $fileName = time() . '_' . $fileUpLoad->getClientOriginalName();
                    $fileType = $fileUpLoad->getMimeType();
                    $fileUpLoad->move(public_path('uploads/file'), $fileName);

                    $file = new File();
                    $file->rel_id = $tasks->id;
                    $file->rel_type = 'task';
                    $file->visible_to_customer = '0';
                    $file->staff_id = Auth::id();
                    $file->task_comment_id = '0';
                    $file->file_name = $fileName;
                    $file->file_type = $fileType;
                    $file->created_by = Auth::id();
                    $file->save();
                }
            }
        }

        // Xử lý tag
        if (!empty($request['tags'])) {
            foreach ($request['tags'] as $tagData) {
                $tag = Tag::firstOrCreate(
                    ['name' => $tagData['name']],
                    ['name' => $tagData['name']]
                );
                $tasks->tags()->attach($tag->id, [
                    'rel_type' => 'task',
                    'tag_order' => $tagData['tag_order'] ?? 1
                ]);
            }
        }

        // Xử lý assigned
        if (isset($request['assigned'])) {
            $assignedIds = [];
            foreach ($request['assigned'] as $assigned) {
                $assignedId = $assigned['id'] ?? 0;
                $tasksAssign = TaskAssign::findOrNew($assignedId);
                $tasksAssign->fill($assigned);
                $tasksAssign->task_id = $tasks->id;
                $tasksAssign->assigned_from = 1;
                $tasksAssign->is_assigned_from_contact = 1;
                $tasksAssign->updated_by = $staff_id;
                $tasksAssign->save();
                $assignedIds[] = $tasksAssign->id;
            }
        }

        // Xử lý custom fields values
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customField = new CustomFieldValue($cfValues);
                $customField->rel_id = $tasks->id;
                $customField->field_to = "tasks";
                $customField->field_id = $tasks->id;
                $customField->save();
            }
        }

        // Xử lý checklist
        if (isset($request['checklist'])) {
            foreach ($request['checklist'] as $cList) {
                $checklist = new TaskChecklist($cList);
                $checklist->task_id = $tasks->id;
                $checklist->finished = 0;
                $checklist->added_from = $staff_id;
                $checklist->finished_from = 0;
                $checklist->list_order = 1;
                $checklist->description = isset($cList['description']) ? $cList['description'] : 'default';
                $checklist->created_by = $staff_id;
                $checklist->save();
            }
        }

        // Lấy dữ liệu task cùng các quan hệ
        $data = Task::where('id', $tasks->id)->with(
            'tags',
            'rel_contract',
            'comments',
            'checklist',
            'timer',
            'assigned:id,first_name,last_name,profile_image',
            'follower',
            'customFields:id,field_to,name',
            'customFieldsValues',
            'files'
        )->first();

        return $data;
    }

    // Cập nhật task
    public function update($id, $request)
    {
        // Lấy user hiện tại
        $user = auth()->guard('api')->user();

        if (!$user) {
            return null;
        }

        // Kiểm tra quyền edit task
        if (!$user->hasPermissionTo('edit task', 'web')) {
            return null;
        }

        $staffId = $user->id;
        $tasks = Task::find($id);

        if (!$tasks) {
            return null;
        }

        // Lấy danh sách role của user
        $role = $user->roles->pluck('id')->toArray();
        $admin_roles = [7, 9, 10]; // Danh sách role có quyền chỉnh sửa tất cả

        // Nếu user không phải admin, kiểm tra quyền chỉnh sửa
        if (!array_intersect($role, $admin_roles)) {
            $canEdit = Task::leftJoin('task_assigned', 'tasks.id', '=', 'task_assigned.task_id')
                ->leftJoin('task_followers', 'tasks.id', '=', 'task_followers.task_id')
                ->leftJoin('project_members', 'tasks.rel_id', '=', 'project_members.project_id')
                ->where(function ($query) use ($staffId) {
                    $query->where('task_assigned.staff_id', $staffId)
                        ->orWhere('tasks.created_by', $staffId)
                        ->orWhere('task_followers.staff_id', $staffId)
                        ->orWhere('project_members.staff_id', $staffId);
                })
                ->where('tasks.id', $id)
                ->exists();

            if (!$canEdit) {
                return null;
            }
        }

        $tasks->fill($request);
        $tasks->updated_by = $staffId;

        // Ghi log hoạt động
        if ($tasks->rel_type == 'project') {
            $this->createProjectActivity($tasks->rel_id, ActivityKey::UPDATE_TASK_BY_PROJECT);
        } elseif ($tasks->rel_type == 'lead') {
            $this->createLeadActivity($tasks->rel_id, ActivityKey::UPDATE_TASK_BY_LEAD);
        }

        $tasks->save();

        // Xử lý tags
        if (!empty($request['tags'])) {
            $currentTagIds = $tasks->tags->pluck('id')->toArray();
            $newTagIds = [];
            foreach ($request['tags'] as $tagData) {
                $tagName = $tagData['name'] ?? null;
                if ($tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName], ['name' => $tagName]);
                    $newTagIds[] = $tag->id;
                    if (!in_array($tag->id, $currentTagIds)) {
                        $tasks->tags()->attach($tag->id, [
                            'rel_type' => 'task',
                            'tag_order' => $tagData['tag_order'] ?? 1
                        ]);
                    }
                }
            }
            $tagsToRemove = array_diff($currentTagIds, $newTagIds);
            if (!empty($tagsToRemove)) {
                $tasks->tags()->detach($tagsToRemove);
            }
        }

        // Xử lý người được phân công
        if (!empty($request['assigned'])) {
            $assignedIds = [];
            foreach ($request['assigned'] as $assigned) {
                $assignedId = $assigned['id'] ?? 0;
                if ($assignedId) {
                    $tasksAssign = TaskAssign::findOrNew($assignedId);
                    $tasksAssign->fill($assigned);
                    $tasksAssign->task_id = $tasks->id;
                    $tasksAssign->staff_id = $assignedId;
                    $tasksAssign->assigned_from = 1;
                    $tasksAssign->is_assigned_from_contact = 1;
                    $tasksAssign->updated_by = $staffId;
                    $tasksAssign->save();
                    $assignedIds[] = $tasksAssign->id;
                }
            }
            TaskAssign::where('task_id', $tasks->id)
                ->whereNotIn('id', $assignedIds)
                ->delete();
        }

        // Xử lý custom fields
        if (!empty($request['customFieldsValues'])) {
            $customFieldIds = [];
            foreach ($request['customFieldsValues'] as $field) {
                $fieldId = $field['id'] ?? 0;
                $customFieldValue = CustomFieldValue::findOrNew($fieldId);
                $customFieldValue->fill($field);
                $customFieldValue->rel_id = $tasks->id;
                $customFieldValue->field_to = 'tasks';
                $customFieldValue->field_id = $fieldId ?? 1;
                $customFieldValue->save();
                $customFieldIds[] = $customFieldValue->id;
            }
            CustomFieldValue::where('rel_id', $tasks->id)
                ->whereNotIn('id', $customFieldIds)
                ->delete();
        }

        // Lấy dữ liệu chi tiết của task sau khi cập nhật
        $data = Task::where('id', $tasks->id)
            ->with(
                'tags',
                'rel_contract',
                'comments',
                'checklist',
                'timer',
                'assigned:id,first_name,last_name,profile_image',
                'follower',
                'customFields:id,field_to,name',
                'customFieldsValues'
            )
            ->first();
        return $data;
    }

    // Xóa task
    public function destroy($id)
    {
        $user = auth()->guard('api')->user();

        if (!$user) {
            return null;
        }

        $role = $user->roles->pluck('id')->toArray();
        $admin_roles = [7, 9, 10]; // Danh sách role admin

        if (!$user->hasPermissionTo('delete task', 'web') && !array_intersect($role, $admin_roles)) {
            return null;
        }

        $tasks = Task::find($id);
        if (!$tasks) {
            return null;
        }

        // Nếu không phải admin, kiểm tra xem có phải người tạo task không
        if (!array_intersect($role, $admin_roles)) {
            if ($tasks->created_by != $user->id) {
                return null;
            }
        }

        $tasks->assigned()->detach();

        // Tạo activity log
        if ($tasks->rel_type == 'project') {
            $this->createProjectActivity($tasks->rel_id, ActivityKey::DELETE_TASK_BY_PROJECT);
        } elseif ($tasks->rel_type == 'lead') {
            $this->createLeadActivity($tasks->rel_id, ActivityKey::DELETE_TASK_BY_LEAD);
        }

        $tasks->delete();

        return $tasks;
    }

    // Thay đổi trang thái
    public function changeStatus($id, $request)
    {
        $staff_id = Auth::id();
        $status = $request['status'];
        $tasks = Task::find($id);
        if (!$tasks) {
            return null;
        }
        $tasks->status = $status;
        $tasks->updated_by = $staff_id;
        $tasks->save();
        if ($tasks && $tasks->rel_type == 'project') {
            $this->createProjectActivity($tasks->rel_id, ActivityKey::CHANGE_STATUS_BY_TASK_IN_PROJECT);
        }
        if ($tasks && $tasks->rel_type == 'lead') {
            $this->createLeadActivity($tasks->rel_id, ActivityKey::CHANGE_STATUS_BY_TASK_IN_LEAD);
        }
        return $tasks;
    }

    // Thay đổi mức độ
    public function changePriority($id, $request)
    {
        $staff_id = Auth::id();
        $priority = $request['priority'];
        $tasks = Task::find($id);
        if (!$tasks) {
            return null;
        }
        $tasks->priority = $priority;
        $tasks->updated_by = $staff_id;
        $tasks->save();
        if ($tasks && $tasks->rel_type == 'project') {
            $this->createProjectActivity($tasks->rel_id, ActivityKey::CHANGE_PRIORITY_BY_TASK_IN_PROJECT);
        }
        if ($tasks && $tasks->rel_type == 'lead') {
            $this->createLeadActivity($tasks->rel_id, key: ActivityKey::CHANGE_PRIORITY_BY_TASK_IN_LEAD);
        }
        return $tasks;
    }

    // Thêm chek list trong task
    public function addChecklist($tasks_id, $request)
    {
        $staff_id = Auth::id();
        $tasks_checklist = new TaskChecklist($request);
        $tasks_checklist->task_id = $tasks_id;
        $tasks_checklist->created_by = $staff_id;
        $tasks_checklist->save();
        $tasks = Task::find($tasks_id);
        if ($tasks && $tasks->rel_type == 'project') {
            $this->createProjectActivity($tasks->rel_id, ActivityKey::CREATE_CHECKLIST_BY_TASK_IN_PROJECT);
        }
        if ($tasks && $tasks->rel_type == 'lead') {
            $this->createLeadActivity($tasks->rel_id, ActivityKey::CREATE_CHECKLIST_BY_TASK_IN_LEAD);
        }
        return $tasks_checklist;
    }

    // Xóa checklist
    public function destroyChecklist($id)
    {
        $tasks_checklist = TaskChecklist::find($id);
        $tasks = Task::where('id', $tasks_checklist->task_id)->first();
        if ($tasks && $tasks->rel_type == 'project') {
            $this->createProjectActivity($tasks->rel_id, ActivityKey::DELETE_CHECKLIST_BY_TASK_IN_PROJECT);
        }
        if ($tasks && $tasks->rel_type == 'lead') {
            $this->createLeadActivity($tasks->rel_id, ActivityKey::DELETE_CHECKLIST_BY_TASK_IN_LEAD);
        }
        $result = $tasks_checklist->delete();
        return $result;
    }

    // Thêm comment theo task
    public function addComment($tasks_id, $request)
    {
        $staff_id = Auth::id();
        $tasks_comment = new TaskComment($request);
        $tasks_comment->task_id = $tasks_id;
        $tasks_comment->staff_id = $staff_id;
        $tasks_comment->created_by = $staff_id;
        $tasks_comment->created_at = Carbon::now();
        $tasks_comment->save();
        $tasks = Task::find($tasks_id);
        if ($tasks && $tasks->rel_type == 'project') {
            $this->createProjectActivity($tasks->rel_id, ActivityKey::CREATE_COMMENT_BY_TASK_IN_PROJECT);
        }
        if ($tasks && $tasks->rel_type == 'lead') {
            $this->createLeadActivity($tasks->rel_id, ActivityKey::CREATE_COMMENT_BY_TASK_IN_LEAD);
        }
        return $tasks_comment;
    }

    // Sửa comment
    public function updateComment($comment_id, $request)
    {
        $staff_id = Auth::id();
        $tasks_comment = TaskComment::find($comment_id);
        if (!$tasks_comment) {
            return null;
        }
        $tasks_comment->fill($request);
        $tasks_comment->updated_by = $staff_id;

        $tasks = Task::where('id', $tasks_comment->task_id)->first();
        if ($tasks && $tasks->rel_type == 'project') {
            $this->createProjectActivity($tasks->rel_id, ActivityKey::UPDATE_COMMENT_BY_TASK_IN_PROJECT);
        }
        if ($tasks && $tasks->rel_type == 'lead') {
            $this->createLeadActivity($tasks->rel_id, ActivityKey::UPDATE_COMMENT_BY_TASK_IN_LEAD);
        }
        $tasks_comment->updated_at = Carbon::now();
        $tasks_comment->save();
        return $tasks_comment;
    }

    // Xóa comment
    public function destroyComment($id)
    {
        $tasks_comment = TaskComment::find($id);
        $tasks = Task::where('id', $tasks_comment->task_id)->first();
        if ($tasks && $tasks->rel_type == 'project') {
            $this->createProjectActivity($tasks->rel_id, ActivityKey::DELETE_COMMENT_BY_TASK_IN_PROJECT);
        }
        if ($tasks && $tasks->rel_type == 'lead') {
            $this->createLeadActivity($tasks->rel_id, ActivityKey::DELETE_COMMENT_BY_TASK_IN_LEAD);
        }
        $result = $tasks_comment->delete();
        return $result;
    }

    // Thêm assigned
    public function addAssignee($request)
    {
        $staff_id = Auth::id();
        $tasks_assign = TaskAssign::create(array_merge($request, ['created_by' => $staff_id]));

        $this->createTaskNotifications($tasks_assign->task_id, 1);

        if ($tasks_assign->task) {
            $rel_id = $tasks_assign->task->rel_id;
            match ($tasks_assign->task->rel_type) {
                'project' => $this->createProjectActivity($rel_id, ActivityKey::CREATE_ASSIGNED_BY_TASK_IN_PROJECT),
                'lead' => $this->createLeadActivity($rel_id, ActivityKey::CREATE_ASSIGNED_BY_TASK_IN_LEAD),
                default => null
            };
        }

        return $tasks_assign;
    }


    // Xóa assiges theo task, staff
    public function destroyAssignee($tasks_id, $staff_id)
    {
        $result = TaskAssign::where('task_id', $tasks_id)->where('staff_id', $staff_id)->delete();
        $tasks = Task::find($tasks_id);
        if ($tasks && $tasks->rel_type == 'project') {
            $this->createProjectActivity($tasks->rel_id, ActivityKey::DELETE_ASSIGNED_BY_TASK_IN_PROJECT);
        }
        if ($tasks && $tasks->rel_type == 'lead') {
            $this->createLeadActivity($tasks->rel_id, ActivityKey::DELETE_ASSIGNED_BY_TASK_IN_LEAD);
        }
        return $result;
    }

    // Thêm follower
    public function addFollower($request)
    {
        $staff_id = Auth::id();
        $tasks_follower = new TaskFollower($request);
        // $tasks_follower->task_id = $tasks_id;
        $tasks_follower->created_by = $staff_id;
        $tasks_follower->save();
        // $tasks = Task::where('id', $tasks_follower->task_id)->first();
        // if ($tasks && $tasks->rel_type == 'project') {
        //     $this->createProjectActivity($tasks->rel_id, 33);
        // }
        // if ($tasks && $tasks->rel_type == 'lead') {
        //     $this->createLeadActivity($tasks->rel_id, 21);
        // }
        return $tasks_follower;
    }

    // Xóa follower
    public function destroyFollower($tasks_id, $staff_id)
    {
        $result = TaskFollower::where('task_id', $tasks_id)->where('staff_id', $staff_id)->delete();
        $tasks = Task::find($tasks_id);
        if ($tasks && $tasks->rel_type == 'project') {
            $this->createProjectActivity($tasks->rel_id, ActivityKey::DELETE_FOLLOWER_BY_TASK_IN_PROJECT);
        }
        if ($tasks && $tasks->rel_type == 'lead') {
            $this->createLeadActivity($tasks->rel_id, ActivityKey::DELETE_FOLLOWER_BY_TASK_IN_LEAD);
        }
        return $result;
    }

    // Danh sách task theo lead
    public function getListByLead($id, $request)
    {
        return $this->applyFilterByRelation($id, 'lead');
    }

    // Danh sách task theo estimate
    public function getListByEstimate($id, $request)
    {
        return $this->applyFilterByRelation($id, 'estimate');
    }

    // Danh sách task theo contract
    public function getListByContract($id, $request)
    {
        return $this->applyFilterByRelation($id, 'contract');
    }

    // Danh sách task theo proposal
    public function getListByProposal($id, $request)
    {
        return $this->applyFilterByRelation($id, 'proposal');
    }

    // Danh sách task theo invoice
    public function getListByInvoice($id, $request)
    {
        return $this->applyFilterByRelation($id, 'invoice');
    }

    // Đếm số lương task theo project
    public function countByProject($id)
    {
        $staff_id = Auth::id();
        $notstarted = Task::where('rel_id', '=', $id)->where('status', 1)->where('rel_type', '=', 'project')->count();
        $inprogress = Task::where('rel_id', '=', $id)->where('status', 2)->where('rel_type', '=', 'project')->count();
        $testing = Task::where('rel_id', '=', $id)->where('status', 3)->where('rel_type', '=', 'project')->count();
        $feedback = Task::where('rel_id', '=', $id)->where('status', 4)->where('rel_type', '=', 'project')->count();
        $complete = Task::where('rel_id', '=', $id)->where('status', 5)->where('rel_type', '=', 'project')->count();
        $menotstarted = Task::join('task_assigned', 'tasks.id', 'task_assigned.task_id')->where('rel_id', '=', $id)->where([['status', '=', 1], ['staff_id', '=', $staff_id], ['rel_type', '=', 'project']])->count();
        $meinprogress = Task::join('task_assigned', 'tasks.id', 'task_assigned.task_id')->where('rel_id', '=', $id)->where([['status', '=', 2], ['staff_id', '=', $staff_id], ['rel_type', '=', 'project']])->count();
        $metesting = Task::join('task_assigned', 'tasks.id', 'task_assigned.task_id')->where('rel_id', '=', $id)->where([['status', '=', 3], ['staff_id', '=', $staff_id], ['rel_type', '=', 'project']])->count();
        $mefeedback = Task::join('task_assigned', 'tasks.id', 'task_assigned.task_id')->where('rel_id', '=', $id)->where([['status', '=', 4], ['staff_id', '=', $staff_id], ['rel_type', '=', 'project']])->count();
        $mecomplete = Task::join('task_assigned', 'tasks.id', 'task_assigned.task_id')->where('rel_id', '=', $id)->where([['status', '=', 5], ['staff_id', '=', $staff_id], ['rel_type', '=', 'project']])->count();

        $data = [
            'notstarted' => $notstarted,
            'inprogress' => $inprogress,
            'testing' => $testing,
            'feedback' => $feedback,
            'complete' => $complete,
            'menotstarted' => $menotstarted,
            'meinprogress' => $meinprogress,
            'metesting' => $metesting,
            'mefeedback' => $mefeedback,
            'mecomplete' => $mecomplete,
        ];
        return $data;
    }

    // Lọc task theo project
    public function filterTaskByProject($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $status = isset($request["status"]) ? json_decode($request["status"]) : null;
        $today = isset($request["today"]) ? (int) $request["today"] : 0;
        $dueDate = isset($request["dueDate"]) ? (int) $request["dueDate"] : 0;
        $upcoming = isset($request["upcoming"]) ? (int) $request["upcoming"] : 0;
        $assigned = isset($request["assigned"]) ? (int) $request["assigned"] : 0;
        $follower = isset($request["follower"]) ? (int) $request["follower"] : 0;
        $notAssigned = isset($request["notAssigned"]) ? (int) $request["notAssigned"] : 0;
        $recurring = isset($request["recurring"]) ? $request["recurring"] : null;
        $billable = isset($request["billable"]) ? $request["billable"] : null;
        $billed = isset($request["billed"]) ? (int) $request["billed"] : 0;
        $member = isset($request["member"]) ? (int) $request["member"] : 0;
        $tasks = Task::leftJoin('task_assigned', 'task_assigned.task_id', '=', 'tasks.id')
            ->leftJoin('task_followers', 'task_followers.task_id', '=', 'tasks.id')
            ->leftJoin('project_members', 'tasks.rel_id', '=', 'project_members.project_id')
            ->where('tasks.rel_id', $id)
            ->where('tasks.rel_type', 'project')
            ->where('task_assigned.staff_id', Auth::user()->staff_id);
        $tasks = $tasks
            ->when(!empty($status), function ($query) use ($status) {
                return $query->whereIn('tasks.status', $status);
            })
            ->when(!empty($today), function ($query) use ($today) {
                if ($today === 1) {
                    return $query->where("tasks.start_date", Carbon::now()->toDateString());
                }
            })
            ->when(!empty($dueDate), function ($query) use ($dueDate) {
                if ($dueDate === 1) {
                    return $query->where("tasks.due_date", '>', Carbon::now()->toDateString());
                }
            })
            ->when(!empty($upcoming), function ($query) use ($upcoming) {
                if ($upcoming === 1) {
                    return $query->where("tasks.start_date", '>', Carbon::now()->toDateString());
                }
            })
            ->when(!empty($notAssigned), function ($query) use ($notAssigned) {
                if ($notAssigned === 1) {
                    return $query->whereNotExists(function ($query) {
                        $query->select("task_assigned.task_id")
                            ->from('task_assigned')
                            ->whereRaw('task_assigned.task_id = tasks.id')
                            ->where("task_assigned.staff_id", Auth::user()->staff_id);
                    });
                }
            })
            ->when(!empty($recurring), function ($query) use ($recurring) {
                return $query->where('tasks.recurring', $recurring);
            })
            ->when(!empty($billable), function ($query) use ($billable) {
                return $query->where("tasks.billable", $billable);
            })
            ->when(!empty($billed), function ($query) use ($billed) {
                if ($billed === 1) {
                    return $query->where("tasks.billed", '=', $billed);
                } else {
                    return $query->where("tasks.billed", '=', 0);
                }
            });

        // Gọi phương thức applyFilterByAsignee nếu assigned = 1
        if ($assigned === 1) {
            $tasks = $this->applyFilterByAssignee($tasks);
        };

        // Gọi phương thức applyFilterByFollower nếu follower = 1
        if ($follower === 1) {
            $tasks = $this->applyFilterByFollower($tasks);
        };

        // Gọi phương thức applyFilterByMember nếu member = 1
        if ($member === 1) {
            $tasks = $this->applyFilterByMember($tasks);
        };

        $tasks = $tasks
            ->with(
                'tags',
                'rel_contract',
                'comments',
                'checklist',
                'timer',
                'assigned',
                'follower',
                'reminder',
                'customFields:id,field_to,name',
                'customFieldsValues'
            )->select('tasks.*')->distinct()->orderBy('tasks.created_at', 'desc');

        return $this->applyPaginate($tasks, $limit, $page);
    }

    // Lọc task theo projectId
    public function filterTaskByProjectId($id, $request)
    {
        return $this->filterTaskByRelation($id, $request, 'project');
    }

    // Sao chép dữ liệu của Task
    public function copyData($id, $request)
    {
        $assign = isset($request["assign"]) ? $request["assign"] : null;
        $status = isset($request["status"]) ? $request["status"] : 0;

        // Tìm Task
        $tasks = Task::find($id);
        if (!$tasks) {
            return null;
        }
        $newTask = $tasks->replicate();
        $newTask->status = $status;
        $newTask->save();

        // Sao chép Tags
        if ($tasks->tags) {
            foreach ($tasks->tags as $tag) {
                $newTask->tags()->attach($tag->id, ['rel_type' => 'task']);
            }
        }

        // Sao chép Comments 
        if ($tasks->comments) {
            foreach ($tasks->comments as $comment) {
                $newComment = $comment->replicate();
                $newComment->task_id = $newTask->id;
                $newComment->save();
            }
        }

        // Sao chép Checklist 
        if ($tasks->checklist) {
            foreach ($tasks->checklist as $checklist) {
                $newChecklist = $checklist->replicate();
                $newChecklist->task_id = $newTask->id;
                $newChecklist->save();
            }
        }

        // Sao chép Assigned 
        if ($assign == 1) {
            $assignedIds = $tasks->assigned->pluck('id')->unique();
            $newTask->assigned()->attach($assignedIds);
        }

        // Sao chép Followers 
        if ($tasks->follower) {
            $followerIds = [];
            foreach ($tasks->follower as $follower) {
                if (!in_array($follower->id, $followerIds)) {
                    $newTask->follower()->attach($follower->id);
                    $followerIds[] = $follower->id;
                }
            }
        }
        // Sao chép Custom Fields
        if ($tasks->customFieldsValues) {
            foreach ($tasks->customFieldsValues as $customFieldValue) {
                $newCustomFieldValue = $customFieldValue->replicate();
                $newCustomFieldValue->rel_id = $newTask->id;
                $newCustomFieldValue->save();
            }
        }

        // Lấy dữ liệu của task mới
        $data = Task::where('id', $newTask->id)->with(
            'tags:id,name',
            'rel_contract',
            'comments',
            'checklist:id,task_id,description',
            'timer',
            'assigned:id,first_name,last_name,profile_image',
            'follower:id,first_name,last_name,profile_image',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->get();

        return $data;
    }

    // Hàm lấy năm
    public function year()
    {
        $year = Carbon::now()->startOfYear()->format('Y');
        $lastYear = Carbon::now()->addYears(-1)->format('Y');
        $data = [$lastYear, $year];
        $data = array_map('intval', $data);
        return $data;
    }

    // Danh sách staff theo task
    public function listStaffByTask($tasks_id)
    {
        $staff = Staff::query()->leftJoin('task_assigned', 'staff.id', '=', 'task_assigned.staff_id')
            ->leftJoin('tasks', 'tasks.id', '=', 'task_assigned.task_id')
            ->where('tasks.id', $tasks_id)
            ->select('staff.*')->distinct()
            ->get();
        return $staff;
    }

    // Lọc task theo contract
    public function filterTaskByContract($id, $request)
    {
        return $this->filterTaskByRelation($id, $request, 'contract');
    }

    // Lọc task theo lead
    public function filterTaskByLead($id, $request)
    {
        return $this->filterTaskByRelation($id, $request, 'lead');
    }

    // Xóa tag trong task
    public function destroyTag($tasks_id, $tag_id)
    {
        $result = Taggables::where('rel_id', $tasks_id)->where('tag_id', $tag_id)->delete();
        $tasks = Task::find($tasks_id);
        if ($tasks && $tasks->rel_type == 'project') {
            $this->createProjectActivity($tasks->rel_id, ActivityKey::DELETE_TAG_BY_TASK_IN_PROJECT);
        }
        if ($tasks && $tasks->rel_type == 'lead') {
            $this->createLeadActivity($tasks->rel_id, ActivityKey::DELETE_TAG_BY_TASK_IN_LEAD);
        }
        return $result;
    }

    // Danh sách task theo ticket
    public function getListByTicket($id, $request)
    {
        return $this->applyFilterByRelation($id, 'ticket');
    }

    // Lọc task theo ticket
    public function filterTaskByTicket($id, $request)
    {
        return $this->filterTaskByRelation($id, $request, 'ticket');
    }

    // Lọc task theo customer
    public function filterTaskByCustomer($id, $request)
    {
        return $this->filterTaskByRelation($id, $request, 'customer');
    }

    // Lọc task theo ticket
    public function filterTaskByExpense($id, $request)
    {
        return $this->filterTaskByRelation($id, $request, 'expense');
    }

    // Lọc task theo invoice
    public function filterTaskByInvoice($id, $request)
    {
        return $this->filterTaskByRelation($id, $request, 'invoice');
    }

    // Lọc task theo proposal
    public function filterTaskByProposal($id, $request)
    {
        return $this->filterTaskByRelation($id, $request, 'proposal');
    }

    public function filterTaskByRelation($id, $request, $relation)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $status = isset($request["status"]) ? json_decode($request["status"]) : null;
        $today = isset($request["today"]) ? (int) $request["today"] : 0;
        $dueDate = isset($request["dueDate"]) ? (int) $request["dueDate"] : 0;
        $upcoming = isset($request["upcoming"]) ? (int) $request["upcoming"] : 0;
        $assigned = isset($request["assigned"]) ? (int) $request["assigned"] : 0;
        $follower = isset($request["follower"]) ? (int) $request["follower"] : 0;
        $notAssigned = isset($request["notAssigned"]) ? (int) $request["notAssigned"] : 0;
        $recurring = isset($request["recurring"]) ? $request["recurring"] : null;
        $billable = isset($request["billable"]) ? $request["billable"] : null;
        $billed = isset($request["billed"]) ? (int) $request["billed"] : 0;
        $member = isset($request["member"]) ? (int) $request["member"] : 0;
        $searchName = isset($request["name"]) ? trim($request["name"]) : null;
        $searchId = isset($request["id"]) ? (int) $request["id"] : null;
        $searchStartDate = isset($request["start_date"]) ? trim($request["start_date"]) : null;

        $tasks = Task::leftJoin('task_assigned', 'task_assigned.task_id', '=', 'tasks.id')
            ->leftJoin('task_followers', 'task_followers.task_id', '=', 'tasks.id')
            ->leftJoin('project_members', 'tasks.rel_id', '=', 'project_members.project_id')
            ->where('tasks.rel_id', $id)
            ->where('tasks.rel_type', $relation);

        $tasks = $tasks
            ->when(!empty($status), function ($query) use ($status) {
                return $query->whereIn('tasks.status', $status);
            })
            ->when(!empty($today), function ($query) use ($today) {
                return $query->where("tasks.start_date", Carbon::now()->toDateString());
            })
            ->when(!empty($dueDate), function ($query) use ($dueDate) {
                return $query->where("tasks.due_date", '>', Carbon::now()->toDateString());
            })
            ->when(!empty($upcoming), function ($query) use ($upcoming) {
                return $query->where("tasks.start_date", '>', Carbon::now()->toDateString());
            })
            ->when(!empty($notAssigned), function ($query) use ($notAssigned) {
                return $query->whereNotExists(function ($query) {
                    $query->select("task_assigned.task_id")
                        ->from('task_assigned')
                        ->whereRaw('task_assigned.task_id = tasks.id')
                        ->where("task_assigned.staff_id", Auth::user()->staff_id);
                });
            })
            ->when(!empty($recurring), function ($query) use ($recurring) {
                return $query->where('tasks.recurring', $recurring);
            })
            ->when(!empty($billable), function ($query) use ($billable) {
                return $query->where("tasks.billable", $billable);
            })
            ->when(!empty($billed), function ($query) use ($billed) {
                return $query->where("tasks.billed", '=', $billed);
            })
            ->when(!empty($searchName), function ($query) use ($searchName) {
                return $query->where("tasks.name", "LIKE", "%{$searchName}%");
            })
            ->when(!empty($searchId), function ($query) use ($searchId) {
                return $query->where("tasks.id", $searchId);
            })
            ->when(!empty($searchStartDate), function ($query) use ($searchStartDate) {
                return $query->where("tasks.start_date", $searchStartDate);
            });

        if ($assigned === 1) {
            $tasks = $this->applyFilterByAssignee($tasks);
        }

        if ($follower === 1) {
            $tasks = $this->applyFilterByFollower($tasks);
        }

        if ($member === 1) {
            $tasks = $this->applyFilterByMember($tasks);
        }

        $tasks = $tasks->with(
            'tags',
            'rel_contract',
            'comments',
            'checklist',
            'timer',
            'assigned',
            'follower',
            'reminder',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->select('tasks.*')->distinct()->orderBy('tasks.created_at', 'desc');

        return $this->applyPaginate($tasks, $limit, $page);
    }


    // Phương thức phân trang
    public function applyPaginate($query, $limit, $page)
    {
        if ($limit > 0) {
            $query = $query->paginate($limit, ['*'], 'page', $page);
        } else {
            $query = $query->get();
        }
        return $query;
    }

    // Phương thức gộp select raw cần thiết
    public function applySelectRaw($query)
    {
        return $query->orderByRaw('FIELD(status, "2", "1", "3", "4", "5")')->orderBy('priority', 'desc')->orderBy('name', 'asc')->with(
            'tags:id,name',
            'rel_contract',
            'comments',
            'checklist:id,task_id,description',
            'timer',
            'assigned:id,first_name,last_name,profile_image',
            'follower:id,first_name,last_name,profile_image',
            'customFields:id,field_to,name',
            'customFieldsValues'
        );
    }

    // Phương thức lọc task theo cụm từ search được nhập vào
    public function applyFilterSearch($query, $search)
    {
        if ($search) {
            $query = $query->where(
                function ($q) use ($search) {
                    $q->where('tasks.name', 'like', '%' . $search . '%')
                        ->orWhere('tasks.start_date', 'like', '%' . $search . '%')
                        ->orWhere('tasks.due_date', 'like', '%' . $search . '%');
                }
            );
        };
        return $query;
    }

    // Phương thức lọc task theo cụm từ search được nhập vào
    public function applyFilterByRelation($id, $request)
    {
        $staff_id = Auth::id();
        $role = 1;
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Task::query();
        if ($role == 1) {
        } else {
            $baseQuery = $baseQuery->leftJoin('task_assigned', 'tasks.id', '=', 'task_assigned.task_id')
                ->where('task_assigned.staff_id', $staff_id)
                ->select('tasks.*');
        }

        $baseQuery = $baseQuery->where('rel_id', '=', $id)->where('rel_type', '=', $request);
        $baseQuery = $this->applyFilterSearch($baseQuery, $search);
        $baseQuery = $this->applySelectRaw($baseQuery);
        return $this->applyPaginate($baseQuery, $limit, $page);
    }

    // Phương thức lọc task theo thành viên của dự án
    public function applyFilterByMember($query)
    {
        return $query->whereExists(function ($query) {
            $query->select("task_assigned.staff_id")
                ->from('task_assigned')
                ->whereRaw('task_assigned.staff_id = project_members.staff_id')
                ->where("task_assigned.staff_id", Auth::user()->staff_id);
        });
    }

    // Phương thức lọc task theo người được phân công của dự án
    public function applyFilterByAssignee($query)
    {
        return $query->whereExists(function ($query) {
            $query->select("task_assigned.task_id")
                ->from('task_assigned')
                ->whereRaw('task_assigned.task_id = tasks.id')
                ->where("task_assigned.staff_id", Auth::user()->staff_id);
        });
    }

    // Phương thức lọc task theo người theo dõi task
    public function applyFilterByFollower($query)
    {
        return $query->whereExists(function ($query) {
            $query->select("task_followers.task_id")
                ->from('task_followers')
                ->whereRaw('task_followers.task_id = tasks.id')
                ->where("task_followers.staff_id", Auth::user()->staff_id);
        });
    }

    // Bulk action task
    public function bulkAction($request)
    {
        $tasks_ids = isset($request["id"]) ? (array)($request["id"]) : [];
        $isProject = isset($request["isProject"]) ? (bool)($request["isProject"]) : 0;
        $project_id = isset($request["project_id"]) ? (int)$request["project_id"] : 0;
        $isStatus = isset($request["isStatus"]) ? (bool)($request["isStatus"]) : 0;
        $status = isset($request["status"]) ? (int)($request["status"]) : 1;
        $isStartDate = isset($request["isStartDate"]) ? (bool) ($request["isStartDate"]) : 0;
        $startDate = isset($request["startDate"]) ? Carbon::parse($request["startDate"]) : Carbon::now();
        $isDueDate = isset($request["isDueDate"]) ? (bool)($request["isDueDate"]) : 0;
        $dueDate = isset($request["dueDate"]) ? Carbon::parse($request["dueDate"]) : Carbon::now();
        $isPriority = isset($request["isPriority"]) ? (bool)($request["isPriority"]) : 0;
        $priority = isset($request["priority"]) ? (int)($request["priority"]) : 1;
        $isTag = isset($request["isTag"]) ? (bool)($request["isTag"]) : 0;
        $tags = isset($request["tag"]) ? (array)($request["tag"]) : [];
        $isAssigned = isset($request["isAssigned"]) ? (bool)($request["isAssigned"]) : 0;
        $assigned = isset($request["assigned"]) ? (array)($request["assigned"]) : [];
        $isFollower = isset($request["isFollower"]) ? (bool) ($request["isFollower"]) : 0;
        $follower = isset($request["follower"]) ? (array)($request["follower"]) : [];

        $isDeleteAll = isset($request["isDeleteAll"]) ? (bool)($request["isDeleteAll"]) : false;
        $isDeleteAssignee = isset($request["isDeleteAssignee"]) ? (bool)($request["isDeleteAssignee"]) : false;
        $isDeleteFollower = isset($request["isDeleteFollower"]) ? (bool)($request["isDeleteFollower"]) : false;

        foreach ($tasks_ids as $tasks_id) {
            $tasks = Task::find($tasks_id);

            // Xử lý xóa task nếu isDeleteAll là true
            if ($isDeleteAll) {
                $tasks->delete();
                continue;
            }

            // Xử lý xóa assignee nếu isDeleteAssignee là true
            if ($isDeleteAssignee) {
                TaskAssign::where('task_id', $tasks_id)->delete();
            }

            // Xử lý xóa follower nếu isDeleteFollower là true
            if ($isDeleteFollower) {
                TaskFollower::where('task_id', $tasks_id)->delete();
            }

            if ($isProject) {
                $tasks->rel_id = $project_id;
                $tasks->rel_type = 'project';
            }

            if ($isStatus) {
                $tasks->status = $status;
            }

            if ($isStartDate) {
                $tasks->start_date = $startDate;
            }

            if ($isDueDate) {
                $tasks->due_date = $dueDate;
            }

            if ($isPriority) {
                $tasks->priority = $priority;
            }

            if ($isTag) {
                $tag = Taggables::where('rel_id', $tasks_id)->get();
                if (isset($request['tag'])) {
                    foreach ($request['tag'] as $key => $tag) {
                        if (isset($tag['id'])) {
                            $tasks->tags()->attach($tag['id'], ['rel_type' => 'task', 'tag_order' => $tag['tag_order']]);
                        } else {
                            $tg = Tag::where('name', $tag['name'])->first();
                            if ($tg === null) {
                                $tg = new Tag($tag);
                                $tg->save();
                            }
                            $tasks->tags()->attach($tg->id, ['rel_type' => 'task', 'tag_order' => $tag['tag_order']]);
                        }
                    }
                }
            }

            if ($isAssigned) {
                $tasksAssign = TaskAssign::where('task_id', $tasks_id)->get();
                if (count($tasksAssign) === 0) {
                } else {
                    TaskAssign::where('task_id', $tasks_id)->delete();
                }
                if (isset($request['assigned'])) {
                    foreach ($request['assigned'] as $pItem) {
                        $item = new TaskAssign($pItem);
                        $item->task_id = $tasks_id;
                        $item->assigned_from = 1;
                        $item->is_assigned_from_contact = 1;
                        $item->created_by = Auth::id();
                        $tasks->taskAssigned()->save($item);
                    }
                }
            }

            if ($isFollower) {
                $tasksFollower = TaskFollower::where('task_id', $tasks_id)->get();
                if (count($tasksFollower) === 0) {
                } else {
                    TaskFollower::where('task_id', $tasks_id)->delete();
                }
                if (isset($request['follower'])) {
                    foreach ($request['follower'] as $pItem) {
                        $item = new TaskFollower($pItem);
                        $item->task_id = $tasks_id;
                        $item->created_by = Auth::id();
                        $tasks->taskFollower()->save($item);
                    }
                }
            }

            $tasks->save();
        }
        $data = Task::whereIn('id', $tasks_ids)->with(
            'tags',
            'rel_contract',
            'comments',
            'checklist',
            'timer',
            'assigned',
            'follower',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->get();

        return $data;
    }

    public function listTaskByMilestones($project_id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $baseQuery = Task::query()->leftJoin('milestones', 'tasks.rel_id', '=', 'milestones.project_id')
            ->where('tasks.rel_id', '=', $project_id)
            ->where('tasks.status', 1)
            ->where('tasks.rel_type', '=', 'project')
            ->select('tasks.*');

        $baseQuery = $this->applySelectRaw($baseQuery);
        return $this->applyPaginate($baseQuery, $limit, $page);
    }

    public function getTasksInCurrentWeek()
    {
        $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d H:i:s');
        $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d H:i:s');

        $tasks = Task::with(
            'tags:id,name',
            'rel_contract',
            'comments',
            'checklist:id,task_id,description',
            'timer',
            'assigned:id,first_name,last_name,profile_image',
            'follower:id,first_name,last_name,profile_image',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->where(function ($query) use ($startOfWeek, $endOfWeek) {
            $query->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->orWhereBetween('updated_at', [$startOfWeek, $endOfWeek]);
        })->whereBetween('status', [1, 5])
            ->orWhere('status', 2)
            ->get();

        $statusCounts = $tasks->groupBy('status')->map->count();

        $tasksByStatus = [];
        for ($status = 1; $status <= 5; $status++) {
            $tasksByStatus[$status] = $statusCounts->get($status, 0);
        }

        $data = [
            'totalTasks' => $tasks->count(),
            'tasksByStatus' => $tasksByStatus,
            'tasks' => $tasks
        ];

        return $data;
    }

    public function countTasksByStatus($request)
    {
        $timeFrame = $request['timeFrame'];

        if (!in_array($timeFrame, [1, 2])) {
            return null;
        }
        if ($timeFrame == 1) {
            $startDate = Carbon::now()->startOfWeek()->toDateString();
            $endDate = Carbon::now()->endOfWeek()->toDateString();
        } elseif ($timeFrame == 2) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        }

        $currentUserId = Auth::id();

        $tasks = DB::table('tasks')
            ->leftJoin('task_assigned', 'tasks.id', '=', 'task_assigned.task_id')
            ->select(
                'tasks.status',
                DB::raw('COUNT(tasks.id) as total'),
                DB::raw('GROUP_CONCAT(tasks.id) as task_id'),
                DB::raw('GROUP_CONCAT(tasks.name) as task_name'),
                DB::raw("SUM(CASE WHEN task_assigned.staff_id = $currentUserId THEN 1 ELSE 0 END) as me_total")
            )
            ->whereBetween('task_assigned.created_at', [$startDate, $endDate])
            ->groupBy('tasks.status')
            ->get();
        $result = [
            'notstarted' => 0,
            'menotstarted' => 0,
            'inprogress' => 0,
            'meinprogress' => 0,
            'testing' => 0,
            'metesting' => 0,
            'feedback' => 0,
            'mefeedback' => 0,
            'complete' => 0,
            'mecomplete' => 0
        ];

        $statusMapping = [
            1 => 'notstarted',
            2 => 'inprogress',
            3 => 'testing',
            4 => 'feedback',
            5 => 'complete',
        ];

        foreach ($tasks as $task) {
            $statusKey = $statusMapping[$task->status] ?? null;
            if ($statusKey) {
                $result[$statusKey] = $task->total;
                $result['me' . $statusKey] = $task->me_total;
            }
        }
        return $result;
    }
    public function taskSummaryByProject($request)
    {
        $staff_id = Auth::id();
        if (!$staff_id) {
            return null;
        }

        $statuses = [
            1 => 'notstarted',
            2 => 'inprogress',
            3 => 'testing',
            4 => 'feedback',
            5 => 'complete'
        ];

        $projects = Project::pluck('name', 'id');

        $taskCounts = Task::where('rel_type', 'project')
            ->whereIn('rel_id', $projects->keys())
            ->selectRaw('rel_id as project_id, status, COUNT(*) as total')
            ->groupBy('rel_id', 'status')
            ->get()
            ->groupBy('project_id');

        $myTaskCounts = Task::join('task_assigned', 'tasks.id', '=', 'task_assigned.task_id')
            ->where('tasks.rel_type', 'project')
            ->whereIn('tasks.rel_id', $projects->keys())
            ->where('task_assigned.staff_id', $staff_id)
            ->selectRaw('tasks.rel_id as project_id, tasks.status, COUNT(*) as mine')
            ->groupBy('tasks.rel_id', 'tasks.status')
            ->get()
            ->groupBy('project_id');

        $result = [];

        foreach ($projects as $project_id => $project_name) {
            $projectData = [
                'project_id' => $project_id,
                'project_name' => $project_name,
                'total_tasks' => 0,
                'my_tasks' => 0,
                'statuses' => []
            ];

            foreach ($statuses as $status => $statusKey) {
                $totalTasks = optional($taskCounts[$project_id] ?? collect())->where('status', $status)->sum('total');
                $myTasks = optional($myTaskCounts[$project_id] ?? collect())->where('status', $status)->sum('mine');


                $projectData['statuses'][$statusKey] = [
                    'total' => $totalTasks,
                    'mine' => $myTasks
                ];

                $projectData['total_tasks'] += $totalTasks;
                $projectData['my_tasks'] += $myTasks;
            }

            $result[] = $projectData;
        }
        return $result;
    }


    public function taskSummaryByCustomer($request)
    {
        $staff_id = Auth::id();
        if (!$staff_id) {
            return null;
        }

        $statuses = [
            1 => 'notstarted',
            2 => 'inprogress',
            3 => 'testing',
            4 => 'feedback',
            5 => 'complete'
        ];

        $customers = Customer::select('id', 'company')->get();

        $totalTasksByCustomer = Task::select('rel_id', 'status', DB::raw('COUNT(*) as total'))
            ->where('rel_type', 'customer')
            ->groupBy('rel_id', 'status')
            ->get()
            ->groupBy('rel_id');

        $myTasksByCustomer = Task::join('task_assigned', 'tasks.id', '=', 'task_assigned.task_id')
            ->select('tasks.rel_id', 'tasks.status', DB::raw('COUNT(*) as total'))
            ->where('tasks.rel_type', 'customer')
            ->where('task_assigned.staff_id', $staff_id)
            ->groupBy('tasks.rel_id', 'tasks.status')
            ->get()
            ->groupBy('rel_id');

        $result = [];

        foreach ($customers as $customer) {
            $customerData = [
                'customer_id' => $customer->id,
                'company_name' => $customer->company,
                'total_tasks' => 0,
                'my_tasks' => 0,
                'statuses' => []
            ];

            foreach ($statuses as $status => $statusKey) {
                $totalTasks = $totalTasksByCustomer->has($customer->id) && $totalTasksByCustomer[$customer->id]->where('status', $status)->first()
                    ? $totalTasksByCustomer[$customer->id]->where('status', $status)->first()->total
                    : 0;

                $myTasks = $myTasksByCustomer->has($customer->id) && $myTasksByCustomer[$customer->id]->where('status', $status)->first()
                    ? $myTasksByCustomer[$customer->id]->where('status', $status)->first()->total
                    : 0;

                $customerData['statuses'][$statusKey] = [
                    'total' => $totalTasks,
                    'mine' => $myTasks
                ];

                $customerData['total_tasks'] += $totalTasks;
                $customerData['my_tasks'] += $myTasks;
            }

            $result[] = $customerData;
        }

        return $result;
    }
}
