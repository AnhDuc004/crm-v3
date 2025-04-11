<?php

namespace Modules\Task\Entities;

use App\Models\BaseModel;
use Modules\Admin\Entities\Staff;

/**
 * @OA\Schema(
 *     schema="TaskAssignModel",
 *     @OA\Xml(name="TaskAssignModel"),
 *     @OA\Property(property="id", type="integer", description="ID hệ thống tự tăng", example=1),
 *     @OA\Property(property="task_id", type="integer", description="ID của task", example=1),
 *     @OA\Property(property="staff_id", type="integer", description="ID của nhân viên", example=1),
 *     @OA\Property(property="assigned_from", type="integer", description="ID người giao việc", example=1),
 *     @OA\Property(property="is_assigned_from_contact", type="tinyInteger", description="Được giao từ liên hệ", example="1"),
 * )
 */

class TaskAssign extends BaseModel
{
    protected $table = 'task_assigned';
    protected $fillable = ['staff_id', 'task_id', 'assigned_from', 'is_assigned_from_contact'];
    protected $hidden = ['create_at', 'updated_at', 'created_by', 'updated_by'];
    public $timestamps = true;

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
