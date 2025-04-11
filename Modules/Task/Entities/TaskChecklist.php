<?php

namespace Modules\Task\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * schema="TaskChecklistModel",
 * @OA\Xml(name="TaskChecklistModel"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="task_id", type="integer", description="ID nhiệm vụ", example="1"),
 * @OA\Property(property="description", type="string", description="Ghi chú", example="abc"),
 * @OA\Property(property="finished", type="integer", description="ID nhân viên", example="1"),
 * @OA\Property(property="added_from", type="integer", description="ID liên hệ", example="1"),
 * @OA\Property(property="finished_from", type="integer", description="ID file", example="1"),
 * @OA\Property(property="list_order", type="integer", description="ID file", example="1"),
 *
 * )
 *
 * Class TaskChecklist
 *
 */
class TaskChecklist extends BaseModel
{
    protected $table = "task_checklist";
    protected $fillable = [
        'task_id',
        'description',
        'finished',
        'added_from',
        'finished_from',
        'list_order'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];
    public $timestamps = true;
}
