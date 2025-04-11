<?php

namespace Modules\Admin\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="Department"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="name", type="string", description="Tên phòng ban", example="VIP"),
 * @OA\Property(property="imap_username", type="string", description="Tên người dùng impa", example="abc"),
 * @OA\Property(property="email", type="string", description="Email", example="a@gmail.com"),
 * @OA\Property(property="email_from_header", type="integer", description="Email từ tiêu đề", example="0.false, 1.true"),
 * @OA\Property(property="host", type="string", description="Tổ chức", example="bhome"),
 * @OA\Property(property="password", type="string", description="Mật khẩu", example="12345szs"),
 * @OA\Property(property="encryption", type="sring", description="Mã hóa", example="DTL"),
 * @OA\Property(property="delete_after_import", type="integer", description="Xóa sau khi nhập", example="1"),
 * @OA\Property(property="calendar_id", type="string", description="Lịch", example="abc"),
 * @OA\Property(property="hide_from_client", type="integer", description="Ẩn cho khách hàng", example="0.false, 1.true"),
 * 
 * 
 *
 * )
 *
 * Class Department
 *
 */
class Department extends BaseModel
{
    protected $table = 'departments';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'imap_username',
        'email',
        'email_from_header',
        'host',
        'encryption',
        'delete_after_import',
        'calendar_id',
        'hide_from_client'
    ];

    protected $hidden = [
        'password',
    ];

    public $timestamps = true;
}
