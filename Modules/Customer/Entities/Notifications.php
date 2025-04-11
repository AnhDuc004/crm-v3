<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Admin\Entities\Staff;

/**
 *
 * @OA\Schema(
 *     @OA\Xml(name="NotificationsModel"),
 *     @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example=1),
 *     @OA\Property(property="is_read", type="interger", description="Trạng thái đã đọc", example=1),
 *     @OA\Property(property="is_read_inline", type="tinyint", description="Trạng thái đọc trực tuyến", example=1),
 *     @OA\Property(property="description", type="string", description="Mô tả thông báo", example="Thông báo VIP"),
 *     @OA\Property(property="from_client_id", type="integer", description="ID của khách hàng gửi thông báo", example=1),
 *     @OA\Property(property="from_fullname", type="string", description="Họ và tên của người gửi thông báo", example="Nguyễn Văn A"),
 *     @OA\Property(property="to_user_id", type="integer", description="ID người nhận thông báo", example=456),
 *     @OA\Property(property="from_company", type="string", description="Tên công ty gửi thông báo", example="Công ty ABC"),
 *     @OA\Property(property="link", type="string", description="Đường dẫn liên kết trong thông báo", example="https://example.com"),
 *     @OA\Property(property="additional_data", type="string", description="Dữ liệu bổ sung kèm thông báo", example="{'key':'value'}"),
 *     @OA\Property(property="created_by", type="integer", description="ID người tạo thông báo", example=789),
 *     @OA\Property(property="updated_by", type="integer", description="ID người cập nhật thông báo", example=321),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo thông báo", example="2025-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật thông báo", example="2025-01-02T15:30:00Z")
 * )
 *
 * Class NotificationsModel
 *
 */
class Notifications extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'is_read', 'is_read_inline', 'description', 'from_client_id', 'from_fullname',
        'to_user_id', 'from_company', 'link', 'additional_data', 'created_at'
    ];

    protected $hidden = [
        'updated_at', 'created_by', 'updated_by'
    ];

    public $timestamps = true;

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'to_user_id');
    }
}
