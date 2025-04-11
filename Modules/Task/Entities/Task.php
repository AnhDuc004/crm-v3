<?php

namespace Modules\Task\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Contract\Entities\Contract;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Admin\Entities\Staff;
use Modules\Customer\Entities\File;
use Modules\Customer\Entities\Tag;
use Modules\Customer\Entities\Taggables;
use Modules\Lead\Entities\Lead;
use Modules\Project\Entities\Project;

/**
 *
 * @OA\Schema(
 * schema="TaskModel",
 * @OA\Xml(name="TaskModel"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="name", type="string", description="Tên công việc", example="Quản trị Bhome"),
 * @OA\Property(property="description", type="string", description="Mô tả", example="công việc mới"),
 * @OA\Property(property="priority", type="integer", description="Mức độ ưu tiên(1.Thấp, 2.Bình thường, 3.Cao, 4.Cấp bách)", example="1"),
 * @OA\Property(property="start_date", type="date", description="Ngày bắt đầu", example="2024-11-25"),
 * @OA\Property(property="due_date", type="date", description="Ngày kết thúc", example="2024-11-27"),
 * @OA\Property(property="finished_date", type="datetime", description="Ngày hoàn thành", example="2024-11-29"),
 * @OA\Property(property="added_from", type="integer", description="Thêm địa chỉ", example="1"),
 * @OA\Property(property="is_added_from_contact", type="tinyInteger", description="Thêm địa chỉ liên lạc", example="1"),
 * @OA\Property(property="status", type="integer", description="Trạng thái(1.Hoạt động, 2.Khóa)", example="1"),
 * @OA\Property(property="recurring_type", type="string", description="Kiểu định kỳ(Tối đa 10 ký tự)", example="tháng"),
 * @OA\Property(property="repeat_every", type="integer", description="Lặp lại ngày", example="1"),
 * @OA\Property(property="recurring", type="integer", description="Định kỳ(1.True, 2.False)", example="1"),
 * @OA\Property(property="is_recurring_from", type="integer", description="Định kỳ từ (1.True, 2.False)", example="1"),
 * @OA\Property(property="cycles", type="integer", description="Chu kỳ(1.True, 2.False)", example="1"),
 * @OA\Property(property="total_cycles", type="integer", description="Tổng chu kỳ(1.True, 2.False)", example="1"),
 * @OA\Property(property="custom_recurring", type="TinyInteger", description="Định kỳ tùy chỉnh(1.True, 2.False)", example="1"),
 * @OA\Property(property="last_recurring_date", type="date", description="Ngày định kỳ cuối", example="2024-11-25"),
 * @OA\Property(property="rel_id", type="integer", description="Mã công việc liên quan", example="1"),
 * @OA\Property(property="rel_type", type="string", description="Tên công việc liên quan(Tối đa 30 ký tự)", example="customer"),
 * @OA\Property(property="is_public", type="tinyInteger", description="Công khai", example="1"),
 * @OA\Property(property="billable", type="tinyInteger", description="Có thể thanh toán", example="1"),
 * @OA\Property(property="billed", type="tinyInteger", description="Đã lập hóa đơn(1.True, 2.False)", example="1"),
 * @OA\Property(property="invoice_id", type="integer", description="Mã hóa đơn ", example="1"),
 * @OA\Property(property="hourly_rate", type="decimal", description="Giá theo giờ", example="1"),
 * @OA\Property(property="milestone", type="integer", description="Cột mốc", example="1"),
 * @OA\Property(property="kanban_order", type="integer", description="Đơn hàng kanban", example="1"),
 * @OA\Property(property="milestone_order", type="integer", description="Thứ tự cột mốc", example="1"),
 * @OA\Property(property="visible_to_client", type="tinyInteger", description="Hiển thị đối với khách hàng", example="1"),
 * @OA\Property(property="deadline_notified", type="integer", description="thông báo hạn chót", example="1"),
 * @OA\Property(
 *     property="tags",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/TagModel"),
 *     description="Danh sách tags liên quan"
 * ),
 * @OA\Property(
 *     property="task_comments",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/TaskCommentModel"),
 *     description="Danh sách comment liên quan"
 * ),
 * @OA\Property(
 *     property="task_checklist",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/TaskChecklistModel"),
 *     description="Danh sách checklist liên quan"
 * ),
 * @OA\Property(
 *     property="task_timers",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/TaskTimerModel"),
 *     description="Danh sách task_timers liên quan"
 * ),
 *  * @OA\Property(
 *     property="task_followers",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/TaskFollowerModel"),
 *     description="Danh sách task_followers liên quan"
 * ),
 * @OA\Property(
 *         property="task_assigned",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/TaskAssignModel"),
 *         description="Danh sách nhân viên được giao"
 *     ),
 * @OA\Property(
 *         property="customfieldsvalues",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/CustomFieldValueModel"),
 *         description="Danh sách customFieldValue liên quan"
 *     ),
 *  @OA\Property(
 *         property="contracts",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ContractModel"),
 *         description="Danh sách contracts liên quan"
 *     ),
 *  @OA\Property(
 *         property="customfields",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/CustomFieldModel"),
 *         description="Danh sách CustomField liên quan"
 *     ),
 *  required={"id", "name","start_date",}
 * )
 * 
 * /**
 * @OA\Schema(
 * schema="CreateTaskRequest",
 * @OA\Xml(name="CreateTaskRequest"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="name", type="string", description="Tên công việc", example="Quản trị Bhome"),
 * @OA\Property(property="description", type="string", description="Mô tả", example="công việc mới"),
 * @OA\Property(property="priority", type="integer", description="Mức độ ưu tiên(1.Thấp, 2.Bình thường, 3.Cao, 4.Cấp bách)", example="1"),
 * @OA\Property(property="start_date", type="date", description="Ngày bắt đầu", example="2024-11-25"),
 * @OA\Property(property="due_date", type="date", description="Ngày kết thúc", example="2024-11-27"),
 * @OA\Property(property="finished_date", type="datetime", description="Ngày hoàn thành", example="2024-11-29"),
 * @OA\Property(property="added_from", type="integer", description="Thêm địa chỉ", example="1"),
 * @OA\Property(property="is_added_from_contact", type="tinyInteger", description="Thêm địa chỉ liên lạc", example="1"),
 * @OA\Property(property="status", type="integer", description="Trạng thái(1.Hoạt động, 2.Khóa)", example="1"),
 * @OA\Property(property="recurring_type", type="string", description="Kiểu định kỳ(Tối đa 10 ký tự)", example="tháng"),
 * @OA\Property(property="repeat_every", type="integer", description="Lặp lại ngày", example="1"),
 * @OA\Property(property="recurring", type="integer", description="Định kỳ(1.True, 2.False)", example="1"),
 * @OA\Property(property="is_recurring_from", type="integer", description="Định kỳ từ (1.True, 2.False)", example="1"),
 * @OA\Property(property="cycles", type="integer", description="Chu kỳ(1.True, 2.False)", example="1"),
 * @OA\Property(property="total_cycles", type="integer", description="Tổng chu kỳ(1.True, 2.False)", example="1"),
 * @OA\Property(property="custom_recurring", type="TinyInteger", description="Định kỳ tùy chỉnh(1.True, 2.False)", example="1"),
 * @OA\Property(property="last_recurring_date", type="date", description="Ngày định kỳ cuối", example="2024-11-25"),
 * @OA\Property(property="rel_id", type="integer", description="Mã công việc liên quan", example="1"),
 * @OA\Property(property="rel_type", type="string", description="Tên công việc liên quan(Tối đa 30 ký tự)", example="customer"),
 * @OA\Property(property="is_public", type="tinyInteger", description="Công khai", example="1"),
 * @OA\Property(property="billable", type="tinyInteger", description="Có thể thanh toán", example="1"),
 * @OA\Property(property="billed", type="tinyInteger", description="Đã lập hóa đơn(1.True, 2.False)", example="1"),
 * @OA\Property(property="invoice_id", type="integer", description="Mã hóa đơn ", example="1"),
 * @OA\Property(property="hourly_rate", type="decimal", description="Giá theo giờ", example="1"),
 * @OA\Property(property="milestone", type="integer", description="Cột mốc", example="1"),
 * @OA\Property(property="kanban_order", type="integer", description="Đơn hàng kanban", example="1"),
 * @OA\Property(property="milestone_order", type="integer", description="Thứ tự cột mốc", example="1"),
 * @OA\Property(property="visible_to_client", type="tinyInteger", description="Hiển thị đối với khách hàng", example="1"),
 * @OA\Property(property="deadline_notified", type="integer", description="thông báo hạn chót", example="1"),
 *     @OA\Property(
 *         property="assigned",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(property="staff_id", type="integer", description="Staff ID to assign the task to"),
 *              @OA\Property(property="task_id", type="integer", description="Staff ID to assign the task to")
 *         ),
 *         description="List of assigned staff members"
 *     ),
 *     @OA\Property(
 *         property="tags",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(property="id", type="integer", description="Tag ID"),
 *             @OA\Property(property="name", type="string", description="Tag name", example="High Priority"),
 *             @OA\Property(property="tag_order", type="integer", description="Tag order", example=2 )
 *         ),
 *         description="List of tags"
 *     ),
 *     @OA\Property(
 *         property="customFieldsValues",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(property="value", type="string", description="Custom field value", example="Engineering")
 *         ),
 *         description="List of custom field values"
 *     ),
 *     @OA\Property(
 *         property="checklist",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(property="description", type="string", description="Checklist item description",example="hello")
 *         ),
 *         description="List of checklist items"
 *     ),
 * )
 * )
 *
 * Class Task
 *
 */
class Task extends BaseModel
{
    protected $table = "tasks";
    protected $fillable = [
        'name',
        'description',
        'priority',
        'start_date',
        'due_date',
        'finished_date',
        'added_from',
        'is_added_from_contact',
        'status',
        'recurring_type',
        'repeat_every',
        'recurring',
        'is_recurring_from',
        'cycles',
        'total_cycles',
        'custom_recurring',
        'last_recurring_date',
        'rel_id',
        'rel_type',
        'is_public',
        'billable',
        'billed',
        'invoice_id',
        'hourly_rate',
        'milestone',
        'kanban_order',
        'milestone_order',
        'last_notification',
        'count_notification',
        'visible_to_client',
        'deadline_notified',
        'created_at',
        'created_by'
    ];

    protected $hidden = [
        'updated_at',
        'updated_by'
    ];
    public $timestamps = true;

    protected $appends = ['customFieldsValues'];

    public function getCustomFieldsValuesAttribute()
    {
        return $this->customFieldsValues()->get();
    }

    public function assigned()
    {
        return $this->belongsToMany(Staff::class, 'task_assigned', 'task_id', 'staff_id');
    }

    public function follower()
    {
        return $this->belongsToMany(Staff::class, 'task_followers', 'task_id', 'staff_id');
    }

    public function taskAssigned()
    {
        return $this->hasMany(TaskAssign::class, 'task_id', 'id');
    }

    public function taskFollower()
    {
        return $this->hasMany(TaskFollower::class, 'task_id', 'id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, Taggables::class, 'rel_id', 'tag_id')->where('rel_type', '=', 'task');
    }

    public function taggable()
    {
        return $this->hasMany(Taggables::class, 'rel_id', 'id');
    }

    public function rel_contract()
    {
        return $this->belongsTo(Contract::class, 'rel_id');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class, 'task_id');
    }

    public function checklist()
    {
        return $this->hasMany(TaskChecklist::class, 'task_id');
    }

    public function timer()
    {
        return $this->hasMany(TaskTimer::class, 'task_id');
    }

    public function reminder()
    {
        return $this->hasMany(Reminder::class, 'rel_id')->where('rel_type', 'task');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'rel_id');
    }
    /**
     *  Get customfieldsvalues hasMany to task
     */
    public function customFieldsValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_id')->where('customfieldsvalues.field_to', '=', 'tasks');
    }
    /**
     *  Get customfields belongsToMany to task
     */
    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, CustomFieldValue::class, 'rel_id', 'field_id')->where('customfieldsvalues.field_to', '=', 'tasks');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'rel_id')->where('rel_type', 'task');
    }
}
