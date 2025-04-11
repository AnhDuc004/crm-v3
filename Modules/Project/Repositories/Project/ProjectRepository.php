<?php

namespace Modules\Project\Repositories\Project;

use App\Traits\LogActivityTrait;
use App\Traits\NotificationsTrait;
use App\Models\ModeHasRole;
use App\Traits\ActivityKey;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\Entities\Staff;
use Modules\Customer\Entities\Contact;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Project\Entities\Project;
use Modules\Customer\Entities\Tag;
use Modules\Customer\Entities\Taggables;
use Modules\Expense\Entities\Expenses;
use Modules\Project\Entities\ProjectMember;
use Modules\Project\Entities\ProjectMilestone;
use Modules\Task\Entities\Task;
use Modules\Task\Entities\TaskAssign;
use Modules\Task\Entities\TaskChecklist;
use Modules\Task\Entities\TaskFollower;
use Modules\Task\Entities\TaskTimer;

class ProjectRepository implements ProjectInterface
{
    use LogActivityTrait;
    use NotificationsTrait;

    public function findId($id)
    {
        $total = 0;
        $project = Project::with('tags', 'customer:id,company', 'staff:id,first_name,last_name')->select('projects.*')->find($id);
        $taskTimer = TaskTimer::leftJoin('tasks', 'task_timers.task_id', '=', 'tasks.id')->where('tasks.rel_id', $id)->where('tasks.rel_type', 'project')->select('task_timers.*')->get();
        foreach ($taskTimer as $value) {
            $startTime = $value->start_time;
            $endTime = $value->end_time;
            $total += ($endTime - $startTime) / 3600;
        }
        return [
            'Project' => $project,
            'TotalLog' => $total,
        ];
    }

    // Danh sách tất cả các dự án
    public function listAll($request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $search = $request['search'] ?? null;
        $status = isset($request['status']) ? json_decode($request['status'], true) : null;
        $by = $request['by'] ?? null;
        $byId = isset($request['byId']) && ctype_digit($request['byId']) ? (int) $request['byId'] : 0;

        // Lấy thông tin user hiện tại
        $user = Auth::user();
        $staff_id = Staff::where('user_id', $user->id)->value('id');

        if (!$staff_id) {
            return null;
        }

        // Kiểm tra role của user
        $user_role = Auth::user()->roles->pluck('id')->toArray();;
        $special_Roles = [7, 9, 10]; // Những role được xem tất cả dự án

        $baseQuery = Project::query()->select('projects.*');

        // Nếu user có quyền đặc biệt, họ được xem tất cả dự án
        if (!array_intersect($user_role, $special_Roles)) {
            $baseQuery->join('project_members', 'project_members.project_id', '=', 'projects.id')
                ->where('project_members.staff_id', $staff_id);
        }

        // Lọc theo loại user: customer hoặc staff
        if ($by === 'customer') {
            $baseQuery->leftJoin('customers', 'customers.id', '=', 'projects.customer_id')
                ->where('customers.id', $byId);
        } elseif ($by === 'staff') {
            if (!$baseQuery->getQuery()->joins) {
                $baseQuery->join('project_members', 'project_members.project_id', '=', 'projects.id');
            }
            $baseQuery->where('project_members.staff_id', $byId);
        }

        // Lọc theo tìm kiếm (name, start_date, deadline)
        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('projects.name', 'like', "%$search%")
                    ->orWhere('projects.start_date', 'like', "%$search%")
                    ->orWhere('projects.deadline', 'like', "%$search%");
            });
        }

        // Lọc theo trạng thái nếu có
        if ($status && is_array($status)) {
            $baseQuery->whereIn('projects.status', $status);
        }

        // Nạp sẵn các quan hệ cần thiết
        $baseQuery->with([
            'tags:id,name',
            'staff:id,first_name,last_name,profile_image',
            'milestone:id,name,project_id',
            'task:id,name,rel_id',
            'customFields:id,field_to,name',
            'customFieldsValues',
            'customer:id,company'
        ]);

        // Sắp xếp theo ngày tạo
        $baseQuery->orderBy('projects.created_at', 'desc');

        // Trả về kết quả (phân trang hoặc lấy toàn bộ)
        return $limit > 0 ? $baseQuery->paginate($limit) : $baseQuery->get();
    }

    public function listSelect() {}

    public function create($request)
    {
        $user = auth()->guard('api')->user();

        if (!$user) {
            return null;
        }

        // Kiểm tra quyền: hoặc có permission create project hoặc là admin
        $role = $user->roles->pluck('id')->toArray();
        $admin_roles = [7, 9, 10]; // Danh sách role admin

        if (!$user->hasPermissionTo('create project', 'web') && !array_intersect($role, $admin_roles)) {
            return null;
        }
        $project_id = $user->id;
        $project = new Project($request);
        $project->created_by = $project_id;
        $project->save();

        if (isset($request['tags'])) {
            foreach ($request['tags'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $project->tags()->attach($tag['id'], ['rel_type' => 'project', 'tag_order' => $tag['tag_order']]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $project->tags()->attach($tg->id, ['rel_type' => 'project', 'tag_order' => $tag['tag_order']]);
                }
            }
        }

        if (isset($request['members'])) {
            $staffIds = [];
            foreach ($request['members'] as $staff) {
                $staffIds[] = $staff['id'];
            }
            $project->staff()->sync($staffIds);
        }

        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $project->id;
                $customFields->field_to = 'projects';
                $customFields->save();
            }
        }
        $this->createProjectActivity($project->id, ActivityKey::CREATED_PROJECT);
        $data = Project::where('id', $project->id)
            ->with('tags', 'staff', 'customFields:id,field_to,name', 'customFieldsValues')
            ->first();
        return $data;
    }

    public function update($id, $request)
    {
        $user = auth()->guard('api')->user();

        if (!$user) {
            return null;
        }

        // Kiểm tra quyền: hoặc có permission edit project hoặc là admin
        $role = $user->roles->pluck('id')->toArray();
        $admin_roles = [7, 9, 10]; // Danh sách role admin

        if (!$user->hasPermissionTo('edit project', 'web') && !array_intersect($role, $admin_roles)) {
            return null;
        }
        $project = Project::find($id);
        $project->fill($request);
        $project->updated_by = $user->id;
        $project->save();
        $this->createProjectActivity($id, ActivityKey::UPDATE_PROJECT);
        $project->taggable()->delete();
        if (isset($request['tags'])) {
            foreach ($request['tags'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $project->tags()->attach($tag['id'], ['rel_type' => 'project', 'tag_order' => $tag['tag_order']]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $project->tags()->attach($tg->id, ['rel_type' => 'project', 'tag_order' => $tag['tag_order']]);
                }
            }
        }
        if (!empty($request['members'])) {
            $staffIds = [];
            foreach ($request['members'] as $staff) {
                $staffIds[] = $staff['staff_id'];
            }
            $project->staff()->sync($staffIds);
        }
        if (!empty($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cValues) {
                $cfvId = isset($cValues['id']) ? $cValues['id'] : 0;
                $customFieldsValues = CustomFieldValue::findorNew($cfvId);
                $customFieldsValues->fill($cValues);
                $customFieldsValues->rel_id = $project->id;
                $customFieldsValues->field_to = 'projects';
                $customFieldsValues->save();
                $customFields[] = $customFieldsValues->id;
            }
            CustomFieldValue::where('rel_id', $project->id)
                ->whereNotIn('id', $customFields)
                ->delete();
        }
        $data = Project::where('id', $project->id)
            ->with('tags', 'staff', 'customer', 'customFields:id,field_to,name', 'customFieldsValues')
            ->first();
        return $data;
    }

    public function destroy($id)
    {
        $user = auth()->guard('api')->user();

        if (!$user) {
            return null;
        }

        // Kiểm tra quyền: hoặc có permission delete project hoặc là admin
        $role = $user->roles->pluck('id')->toArray();
        $admin_roles = [7, 9, 10]; // Danh sách role admin

        if (!$user->hasPermissionTo('delete project', 'web') && !array_intersect($role, $admin_roles)) {
            return null;
        }
        $project = Project::find($id);
        $this->createProjectActivity($id, ActivityKey::DELETE_PROJECT);
        $project->delete();
        return $project;
    }

    public function copy($id, $request)
    {
        $project = Project::where('id', $id)->with('task', 'milestone', 'staff')->first();
        $option = $request['option'];
        $task = $option['task'];
        $checklist = $option['checklist'];
        $follower = $option['follower'];
        $assigned = $option['assigned'];
        $milestone = $option['milestone'];
        $member = $option['member'];
        $status = $request['status'];
        $customer = $request['customer'];
        $start_date = $request['start_date'];
        $deadline = $request['deadline'];
        $projectNew = new Project();

        if ($task === 1 && $member === 0 && $milestone === 0) {
            $this->addProject($project, $customer, $start_date, $deadline, $projectNew);
            $this->addTask($project['task'], $status, $projectNew, $checklist, $assigned, $follower);
        } elseif ($task === 1 && $member === 1 && $milestone === 0) {
            $this->addProject($project, $customer, $start_date, $deadline, $projectNew);
            $this->addTask($project['task'], $status, $projectNew, $checklist, $assigned, $follower);
            foreach ($project['staff'] as $staff) {
                $this->addProjectMember($staff, $projectNew);
            }
        } elseif ($task === 1 && $member === 0 && $milestone === 1) {
            $this->addProject($project, $customer, $start_date, $deadline, $projectNew);
            $this->addTask($project['task'], $status, $projectNew, $checklist, $assigned, $follower);
            foreach ($project['milestone'] as $milestone) {
                $this->addProjectMillestone($milestone, $projectNew);
            }
        } elseif ($task === 1 && $member === 1 && $milestone === 1) {
            $this->addProject($project, $customer, $start_date, $deadline, $projectNew);
            $this->addTask($project['task'], $status, $projectNew, $checklist, $assigned, $follower);
            foreach ($project['staff'] as $staff) {
                $this->addProjectMember($staff, $projectNew);
            }
            foreach ($project['milestone'] as $milestone) {
                $this->addProjectMillestone($milestone, $projectNew);
            }
        } elseif ($task === 0 && $member === 1 && $milestone === 0) {
            $this->addProject($project, $customer, $start_date, $deadline, $projectNew);
            foreach ($project['staff'] as $staff) {
                $this->addProjectMember($staff, $projectNew);
            }
        } elseif ($task === 0 && $member === 0 && $milestone === 1) {
            $this->addProject($project, $customer, $start_date, $deadline, $projectNew);
            foreach ($project['milestone'] as $milestone) {
                $this->addProjectMillestone($milestone, $projectNew);
            }
        } elseif ($task === 0 && $member === 1 && $milestone === 1) {
            $this->addProject($project, $customer, $start_date, $deadline, $projectNew);
            foreach ($project['staff'] as $staff) {
                $this->addProjectMember($staff, $projectNew);
            }
            foreach ($project['milestone'] as $milestone) {
                $this->addProjectMillestone($milestone, $projectNew);
            }
        } elseif ($task === 0 && $member === 0 && $milestone === 0) {
            $this->addProject($project, $customer, $start_date, $deadline, $projectNew);
        }

        $this->createProjectActivity($id, ActivityKey::COPY_PROJECT);

        $data = Project::where('id', $projectNew['id'])
            ->with('customer:id,company', 'tags:id,name', 'staff:id,first_name,last_name', 'milestone:id,name', 'task:id,name,rel_id')
            ->first();
        return $data;
    }


    public function addProject($project, $customer, $start_date, $deadline, $projectNew)
    {
        // $projectNew->fill($project);
        $projectNew->name = $project->name;
        $projectNew->billing_type = $project->billing_type;
        $projectNew->customer_id = $customer;
        $projectNew->start_date = $start_date;
        $projectNew->deadline = $deadline;
        $projectNew->status = $project->status;
        $projectNew->description = $project->description;
        $projectNew->billing_type = $project->billing_type;
        $projectNew->date_finished = $project->date_finished;
        $projectNew->progress = $project->progress;
        $projectNew->progress_from_tasks = $project->progress_from_tasks;
        $projectNew->project_cost = $project->project_cost;
        $projectNew->project_rate_per_hour = $project->project_rate_per_hour;
        $projectNew->estimated_hours = $project->estimated_hours;
        $projectNew->created_by = Auth::user()->id;
        $projectNew->save();
        foreach ($project['tags'] as $tag) {
            $pTag = new Taggables();
            $pTag->rel_id = $projectNew->id;
            $pTag->tag_id = $tag->id;
            $pTag->rel_type = 'project';
            $pTag->tag_order = $tag->order;
            $pTag->save();
        }
    }

    public function addProjectMember($staff, $projectList)
    {
        $pStaff = new ProjectMember();
        $pStaff->project_id = $projectList->id;
        $pStaff->staff_id = $staff->id;
        $pStaff->save();
    }

    public function addProjectMillestone($milestone, $projectList)
    {
        $pMilestone = new ProjectMilestone();
        $pMilestone->name = $milestone->name;
        $pMilestone->description = $milestone->description;
        $pMilestone->description_visible_to_customer = $milestone->description_visible_to_customer;
        $pMilestone->due_date = $milestone->due_date;
        $pMilestone->project_id = $projectList->id;
        $pMilestone->color = $milestone->color;
        $pMilestone->milestone_order = $milestone->milestone_order;
        $pMilestone->datecreated = Carbon::now();
        $pMilestone->save();
    }

    public function addTask($pTasks, $status, $projectList, $checklist, $assigned, $follower)
    {
        foreach ($pTasks as $pTask) {
            // $task = new Task();
            // $task
            $task = $pTask->replicate();
            // $task->name = $pTask->name;
            $task->status = $status;
            $task->rel_id = $projectList->id;
            $task->rel_type = 'project';
            // $task->start_date = $pTask->start_date;
            // $task->due_date = $pTask->due_date;
            // $task->description = $pTask->description;
            // $task->priority = $pTask->priority;
            // $task->finished_date = $pTask->finished_date;
            // $task->added_from = $pTask->added_from;
            // $task->is_added_from_contact = $pTask->is_added_from_contact;
            // $task->recurring_type = $pTask->recurring_type;
            // $task->repeat_every = $pTask->repeat_every;
            // $task->recurring = $pTask->recurring;
            // $task->is_recurring_from = $pTask->is_recurring_from;
            // $task->cycles = $pTask->cycles;
            // $task->total_cycles = $pTask->total_cycles;
            // $task->custom_recurring = $pTask->custom_recurring;
            // $task->last_recurring_date = $pTask->last_recurring_date;
            // $task->is_public = $pTask->is_public;
            // $task->billable = $pTask->billable;
            // $task->billed = $pTask->billed;
            // $task->invoice_id = $pTask->invoice_id;
            // $task->hourly_rate = $pTask->hourly_rate;
            // $task->milestone = $pTask->milestone;
            // $task->kanban_order = $pTask->kanban_order;
            // $task->milestone_order = $pTask->milestone_order;
            // $task->visible_to_client = $pTask->visible_to_client;
            // $task->deadline_notified = $pTask->deadline_notified;
            $task->created_by = Auth::user()->id;
            $task->save();
            if ($checklist === 1) {
                $this->copyCheckListOfTask($pTask['checklist'], $task);
            }
            if ($assigned === 1) {
                $this->copyAssignedOfTask($pTask['assigned'], $task);
            }
            if ($follower === 1) {
                $this->copyFollowerOfTask($pTask['follower'], $task);
            }
        }
    }
    public function copyAssignedOfTask($assigneds, $task)
    {
        foreach ($assigneds as $assigned) {
            $tAssigned = new TaskAssign();
            $tAssigned->task_id = $task->id;
            $tAssigned->staff_id = $assigned->staff_id;
            $tAssigned->save();
        }
    }

    public function copyFollowerOfTask($followers, $task)
    {
        foreach ($followers as $follower) {
            $tFollower = new TaskFollower();
            $tFollower->task_id = $task->id;
            $tFollower->staff_id = $follower->staff_id;
            $tFollower->save();
        }
    }

    public function copyCheckListOfTask($checklists, $task)
    {
        foreach ($checklists as $checklist) {
            $tChecklist = new TaskChecklist();
            $tChecklist->task_id = $task->id;
            $tChecklist->description = $checklist->description;
            $tChecklist->finished = $checklist->finished;
            $tChecklist->added_from = $checklist->added_from;
            $tChecklist->finished_from = $checklist->finished_from;
            $tChecklist->list_order = $checklist->list_order;
            $tChecklist->save();
        }
    }

    public function getListByCustomer($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 1;

        $baseQuery = Project::leftJoin('customers', 'customers.id', '=', 'projects.customer_id')->where('customers.id', '=', $id);

        if ($search) {
            $baseQuery = $baseQuery->where(function ($q) use ($search) {
                $q->where('projects.name', 'like', '%' . $search . '%')
                    ->orWhere('projects.start_date', 'like', '%' . $search . '%')
                    ->orWhere('projects.deadline', 'like', '%' . $search . '%');
            });
        }
        $project = $baseQuery->with('customer', 'staff', 'tags', 'customFields:id,field_to,name', 'customFieldsValues')->select('projects.*')->orderBy('projects.created_at', 'desc');
        if ($limit > 0) {
            $project = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $project = $baseQuery->get();
        }
        return $project;
    }

    // hàm này thực chất là hàm creat a project
    // tạm thời comment chờ phản hồi từ FE sau đó sẽ xóa
    // public function createByCustomer($id, $request)
    // {
    //     try {
    //         $customer = Customer::find($id);
    //         if (!$customer) {
    //             return Result::fail(static::errCustomerMess);
    //         }
    //         $project = new Project($request);
    //         $project->customer_id = $customer->id;
    //         $project->created_by = Auth::user()->id;
    //         $project->save();
    //         if (isset($request['tags'])) {
    //             foreach ($request['tags'] as $key => $tag) {
    //                 if (isset($tag['id'])) {
    //                     $project->tags()->attach($tag['id'], ['rel_type' => 'project', 'tag_order' => $tag['tag_order']]);
    //                 } else {
    //                     $tg = Tag::where('name',  $tag['name'])->first();
    //                     if ($tg === null) {
    //                         $tg = new Tag($tag);
    //                         $tg->save();
    //                     }
    //                     $project->tags()->attach($tg->id, ['rel_type' => 'project', 'tag_order' => $tag['tag_order']]);
    //                 }
    //             }
    //         }
    //         if (isset($request['members'])) {
    //             $staffIds = [];
    //             foreach ($request['members'] as $staff) {
    //                 $staffIds[] = $staff["staff_id"];
    //             }
    //             $project->staff()->sync($staffIds);
    //         }
    //         if (isset($request['customFieldsValues'])) {
    //             foreach ($request['customFieldsValues'] as $cfValues) {
    //                 $customFields = new CustomFieldValue($cfValues);
    //                 $customFields->relid = $project->id;
    //                 $customFields->fieldto = "projects";
    //                 $customFields->save();
    //             }
    //         }
    //         $this->createProjectActivity($project->id, 2);
    //         $data = Project::where('id', $project->id)->with(
    //             'customer',
    //             'staff',
    //             'tags',
    //             'customFields:id,field_to,name',
    //             'customFieldsValues'
    //         )->first();
    //         return Result::success($data);
    //     } catch (Exception $e) {
    //         Log::error($e->getMessage());
    //         return Result::fail('Tạo mới dự án thất bại');
    //     }
    // }

    public function countByCustomer($id)
    {
        /*  NotStarted <=> status = 1
            InProgress <=> status = 2
            OnHold <=> status = 3
            Cancelled <=> status = 4
            Finished <=> status = 5
        */
        $notStarted = Project::where('customer_Id', $id)->where('status', 1)->count();
        $inProgress = Project::where('customer_Id', $id)->where('status', 2)->count();
        $onHold = Project::where('customer_Id', $id)->where('status', 3)->count();
        $cancelled = Project::where('customer_Id', $id)->where('status', 4)->count();
        $finished = Project::where('customer_Id', $id)->where('status', 5)->count();
        return [
            'NotStarted' => $notStarted,
            'InProgress' => $inProgress,
            'OnHold' => $onHold,
            'Cancelled' => $cancelled,
            'Finished' => $finished,
        ];
    }

    // hàm này thực chất là hàm getALL project
    // tạm thời comment chờ phản hồi từ FE sau đó sẽ xóa
    public function getListByStaff($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = Project::leftJoin('project_members', 'project_members.project_id', '=', 'projects.id')->leftJoin('staff', 'staff.id', '=', 'project_members.staff_id')->where('project_members.staff_id', '=', $id);

        if ($search) {
            $baseQuery = $baseQuery->where(function ($q) use ($search) {
                $q->where('projects.name', 'like', '%' . $search . '%')
                    ->orWhere('projects.start_date', 'like', '%' . $search . '%')
                    ->orWhere('projects.deadline', 'like', '%' . $search . '%');
            });
        }
        $project = $baseQuery->with('customer', 'staff', 'tags', 'customFields:id,field_to,name', 'customFieldsValues')->select('projects.*')->orderBy('projects.created_at', 'desc');
        if ($limit > 0) {
            $project = $baseQuery->paginate($limit);
        } else {
            $project = $baseQuery->get();
        }

        return $project;
    }

    public function countOverview($id)
    {
        // Đếm và tính phần trăm cho OPEN TASKS ở Overview
        $totalTask = Task::where('rel_id', $id)->where('rel_type', 'project')->count();
        $openTask = Task::where('rel_id', $id)->where('rel_type', 'project')->where('status', '!=', '5')->count();

        if ($totalTask != 0) {
            $percentOfTask = (($totalTask - $openTask) / $totalTask) * 100;
            return [
                'openTask' => $openTask,
                'totalTask' => $totalTask,
                'percentOfTask' => $percentOfTask,
            ];
        } else {
            return [
                'openTask' => 0,
                'totalTask' => 0,
                'percentOfTask' => 0,
            ];
        }
    }

    function countDayLeft($id)
    {
        // Đếm và tính phần trăm cho DAYS LEFT ở OVerview
        $project = Project::find($id);
        //Lấy ngày của startDate
        $startDate = strtotime($project->start_date);
        //Lấy ngày của Deadline
        $deadLine = strtotime($project->deadline);
        //Lấy ngày hiện tại
        $today = Carbon::today();
        $day = strtotime($today);

        $totalDayLeft = ($deadLine - $startDate) / 86400;
        if ($deadLine <= $day) {
            $dayLeft = 0;
        } else {
            $dayLeft = ($deadLine - $day) / 86400;
        }
        if ($totalDayLeft > 0) {
            $percentOfDayLeft = ($dayLeft / $totalDayLeft) * 100;
        } else {
            $percentOfDayLeft = 0;
        }

        //Tính tổng tiền của expenses
        $expenseve = Expenses::where('project_id', $id)->sum('amount');

        return [
            'totalDayLeft' => $totalDayLeft,
            'dayLeft' => $dayLeft,
            'percentOfDayLeft' => $percentOfDayLeft,
            'totalExpenses' => $expenseve,
        ];
    }

    public function countByStatus()
    {
        /*  NotStarted <=> status = 1
            InProgress <=> status = 2
            OnHold <=> status = 3
            Cancelled <=> status = 4
            Finished <=> status = 5
        */
        $notStarted = Project::leftJoin('project_members', 'project_members.project_id', '=', 'projects.id')
            ->where('project_members.staff_id', Auth::user()->staffid)
            ->where('projects.status', 1)
            ->count();
        $inProgress = Project::leftJoin('project_members', 'project_members.project_id', '=', 'projects.id')
            ->where('project_members.staff_id', Auth::user()->staffid)
            ->where('projects.status', 2)
            ->count();
        $onHold = Project::leftJoin('project_members', 'project_members.project_id', '=', 'projects.id')
            ->where('project_members.staff_id', Auth::user()->staffid)
            ->where('projects.status', 3)
            ->count();
        $cancelled = Project::leftJoin('project_members', 'project_members.project_id', '=', 'projects.id')
            ->where('project_members.staff_id', Auth::user()->staffid)
            ->where('projects.status', 4)
            ->count();
        $finished = Project::leftJoin('project_members', 'project_members.project_id', '=', 'projects.id')
            ->where('project_members.staff_id', Auth::user()->staffid)
            ->where('projects.status', 5)
            ->count();

        return [
            'NotStarted' => $notStarted,
            'InProgress' => $inProgress,
            'OnHold' => $onHold,
            'Cancelled' => $cancelled,
            'Finished' => $finished,
        ];
    }

    public function getContactByProject($id)
    {
        $project = Project::where('id', $id)->first();
        $customer = $project->customer_id;
        $contact = Contact::where('customer_Id', $customer)->with('customer:customer_Id,company')->select('contacts.first_name', 'contacts.last_name', 'contacts.email', 'contacts.customer_id')->get();
        return $contact;
    }

    public function addMember($project_id, $request)
    {
        $project = Project::find($project_id);

        if (isset($request['members'])) {
            $staffIds = [];
            foreach ($request['members'] as $staff) {
                $staffIds[] = $staff["staff_id"];
            }
            $project->staff()->sync($staffIds);
            $this->createProjectNotifications($project_id, ActivityKey::CREATED_PROJECT);
        }
        $this->createProjectActivity($project->id, ActivityKey::CREATE_MEMBER_BY_PROJECT);
        return $project->load('staff');
    }

    public function destroyMember($project_id, $staff_id)
    {
        $result = ProjectMember::where('project_id', $project_id)->where('staff_id', $staff_id)->delete();
        $this->createProjectNotifications($project_id, ActivityKey::DELETE_MEMBER_BY_PROJECT);
        return $result;
    }

    public function bulkAction($request)
    {
        $projectIds = $request['project_ids'] ?? [];
        $action = $request['action'] ?? null;

        if (empty($projectIds)) {
            return null;
        }

        $projects = Project::whereIn('id', $projectIds)->get();

        switch ($action) {
            case 'delete':
                Project::whereIn('id', $projectIds)->delete();
                return null;

            case 'update_status':
                if (!isset($request['status'])) {
                    return null;
                }
                Project::whereIn('id', $projectIds)->update(['status' => $request['status']]);
                return null;

            case 'update_creator':
                if (!isset($request['created_by']) && !isset($request['updated_by'])) {
                    return null;
                }

                $updateData = [];
                if (!empty($request['created_by'])) {
                    $updateData['created_by'] = $request['created_by'];
                }
                if (!empty($request['updated_by'])) {
                    $updateData['updated_by'] = $request['updated_by'];
                }

                Project::whereIn('id', $projectIds)->update($updateData);
                return null;
            default:
                return null;
        }
    }
}
