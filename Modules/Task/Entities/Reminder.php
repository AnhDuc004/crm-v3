<?php

namespace Modules\Task\Entities;
use Modules\Customer\Entities\Customer;
use Modules\Admin\Entities\Staff;
use Illuminate\Database\Eloquent\Model;

/**
 *
 * @OA\Schema(
 * schema="ReminderModel",
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="rel_id", type="integer", description="Mã công việc liên quan", example="1"),
 * @OA\Property(property="rel_type", type="string", description="Tên công việc liên quan", example="customer"),
 * @OA\Property(property="description", type="string", description="Mô tả", example="Nghỉ ngơi đi không vội"),
 * @OA\Property(property="staff_id", type="integer", description="Chuyển nhắc nhở sang", example="1"),
 * @OA\Property(property="date", type="date", description="Ngày bắt đầu", example="2024-10-10"),
 * @OA\Property(property="deadline", type="date", description="Ngày kết thúc", example="2024-10-10"),
 * @OA\Property(property="is_notified", type="integer", description="Có thông báo không", example="1"),
 * @OA\Property(property="notify_by_email", type="integer", description="Gửi email cho cả nhắc nhở", example="1"),
 *
 * )
 *
 * Class Reminder
 *
 */
class Reminder extends Model
{
    protected $table = 'reminders';

    protected $fillable = [
        'rel_id',
        'rel_type',
        'staff_id',
        'description',
        'date',
        'deadline',
        'is_notified',
        'notify_by_email'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];

    public $timestamps = true;
    
    /**
     *  Get customer belongsTo to reminder
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'rel_id');
    }

    /**
     *  Get staff belongsTo to reminder
     */
    public function staffs()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
