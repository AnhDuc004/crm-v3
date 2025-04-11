<?php

namespace Modules\Task\Entities;

use App\Models\BaseModel;
use Modules\Customer\Entities\Contract;
use Modules\Admin\Entities\Staff;
use Modules\Customer\Entities\Tag;
use Modules\Customer\Entities\Taggables;

/**
 *
 * @OA\Schema(
 * schema="TaskTimerModel",
 * @OA\Xml(name="TaskTimerModel"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="task_id", type="integer", description="ID nhiệm vụ", example="1"),
 * @OA\Property(property="start_time", type="string", description="giờ bát đầu", example="2432005"),
 * @OA\Property(property="end_time", type="string", description="giờ kết thúc", example="26062002"),
 * @OA\Property(property="staff_id", type="integer", description="ID nhân viên", example="1"),
 * @OA\Property(property="hourly_rate", type="decimal", description="lương theo giờ", example="1"),
 * @OA\Property(property="note", type="string", description="ghi chú", example="ghi chú"),
 * required={"id", "task_id", "start_time", "hourly_rate","note"}
 *
 * )
 * )
 * Class TaskTimer
 *
 */
class TaskTimer extends BaseModel
{
    protected $table = "task_timers";
    protected $fillable = [
        'task_id',
        'start_time',
        'end_time',
        'staff_id',
        'hourly_rate',
        'note'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];
    public $timestamps = true;

    public function tags()
    {
        return $this->belongsToMany(Tag::class, Taggables::class, 'rel_id', 'tag_id')->where('rel_type', '=', 'timesheet');
    }

    public function taggable()
    {
        return $this->hasMany(Taggables::class, 'rel_id', 'id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
