<?php

namespace Modules\Task\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * schema="TaskFollowerModel",
 * @OA\Xml(name="TaskFollowerModel"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="staff_id", type="integer", description="ID nhân viên ", example="1"),
 * @OA\Property(property="task_id", type="integer", description="ID nhiệm vụ", example="1"),
 *
 * )
 *
 * Class TaskFollower
 *
 */
class TaskFollower extends BaseModel
{
    protected $table = "task_followers";
    protected $fillable = [
        'staff_id',
        'task_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];
    public $timestamps = true;
}
