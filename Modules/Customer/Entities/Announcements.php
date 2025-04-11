<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="Announcements"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tư sinh", example="1"),
 * @OA\Property(property="name", type="string", description="Tên thông báo", example="abc"),
 * @OA\Property(property="message", type="string", description="Nội dung thông báo", example="Nguyễn"),
 * @OA\Property(property="showtousers", type="integer", description="Người trưng bày", example="1"),
 * @OA\Property(property="showtostaff", type="integer", description="Hiển thị cho nhân viên xem", example="1"),
 * @OA\Property(property="showname", type="integer", description="Hiển thị tên", example="1"),
 * @OA\Property(property="userid", type="string", description="Người dùng", example="1"),
 *
 * )
 *
 * Class Announcements
 *
 */
class Announcements extends BaseModel
{
    protected $table = 'announcements';

    protected $primaryKey = 'announcementid';

    protected $fillable = [
        'name', 'message', 'showtousers', 'showtostaff', 'showname', 'showadded', 'userid'
    ];

    public $timestamps = false;
}