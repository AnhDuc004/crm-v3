<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Modules\Customer\Entities\Notifications;
use Modules\Project\Entities\ProjectMember;
use Modules\Task\Entities\TaskAssign;
use Modules\Task\Entities\TaskFollower;

trait NotificationsTrait
{
    public function createTaskNotifications($task_id, $key)
    {
        $models = [
            1 => TaskAssign::class,
            2 => TaskFollower::class
        ];

        if (!isset($models[$key])) {
            return;
        }

        $tasks = $models[$key]::where('task_id', $task_id)->with('staff')->get();
        $description = $key === 1 ? 'task_added_you_as_assigned' : 'task_added_you_as_follower';

        foreach ($tasks as $task) {
            $this->createNotification($task->staff, $task_id, $description, 'task');
        }
    }

    public function createProjectNotifications($project_id, $key)
    {
        if ($key === 1) {
            $projects = ProjectMember::where('project_id', $project_id)->with('staff')->get();
            foreach ($projects as $project) {
                $this->createNotification($project->staff, $project_id, 'staff_added_as_project_member', 'project');
            }
        }
    }

    private function createNotification($staff, $entity_id, $description, $type)
    {
        if (!$staff) {
            return;
        }

        Notifications::create([
            'is_read' => 0,
            'is_read_inline' => 0,
            'from_client_id' => 0,
            'from_company' => null,
            'additional_data' => null,
            'created_by' => Auth::id(),
            'description' => $description,
            'to_user_id' => $staff->id,
            'from_fullname' => $staff->first_name . ' ' . $staff->last_name,
            'link' => "#{$type}_id={$entity_id}"
        ]);
    }
}
