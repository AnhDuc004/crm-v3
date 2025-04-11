<?php

namespace Modules\Task\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * schema="TaskCommentModel",
 * @OA\Xml(name="TaskCommentModel"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="content", type="string", description="Nội dung", example="abc"),
 * @OA\Property(property="task_id", type="integer", description="ID nhiệm vụ", example="1"),
 * @OA\Property(property="staff_id", type="integer", description="ID nhân viên", example="1"),
 * @OA\Property(property="contact_id", type="integer", description="ID liên hệ", example="1"),
 * @OA\Property(property="file_id", type="integer", description="ID file", example="1"),
 * )
 *
 * Class TaskComment
 *
 */

class TaskComment extends BaseModel
{
    protected $table = "task_comments";
    protected $fillable = [
        'content',
        'task_id',
        'staff_id',
        'contact_id',
        'file_id'
    ];

    protected $hidden = [
        'created_by',
        'updated_by'
    ];
    public $timestamps = true;
}
