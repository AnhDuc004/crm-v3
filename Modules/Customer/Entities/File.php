<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * schema="FileModel",
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="rel_id", type="integer", description="Mã công việc liên quan", example="1"),
 * @OA\Property(property="rel_type", type="string", description="Tên công việc liên quan", example="customer"),
 * @OA\Property(property="file_name", type="string", description="Đường dẫn ảnh", example="image/anh.jpg"),
 * @OA\Property(property="file_type", type="string", description="Kiểu đường dẫn ảnh", example="image/jpeg"),
 * @OA\Property(property="staff_id", type="integer", description="Mã nhân viên", example="1"),
 * @OA\Property(property="contact_id", type="integer", description="Mã người liên hệ", example="1"),
 * @OA\Property(property="task_comment_id", type="integer", description="Mã công việc", example="1"),
 *
 *
 *
 * )
 *
 * Class File
 *
 */
class File extends BaseModel
{
    protected $table = 'files';
    protected $fillable = ['rel_id', 'rel_type', 'file_name', 'file_type', 'staff_id', 'contact_id', 'task_comment_id', 'visible_to_customer'];
    protected $hidden = ['created_by', 'updated_by', 'attachment_key', 'external', 'external_link', 'thumbnail'];
    public $timestamps = true;
}
