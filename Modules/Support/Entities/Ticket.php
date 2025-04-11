<?php

namespace Modules\Support\Entities;

use App\Models\BaseModel;
use Modules\Customer\Entities\Contact;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Admin\Entities\Department;
use Modules\Customer\Entities\Service;
use Modules\Admin\Entities\Staff;
use Modules\Customer\Entities\Tag;
use Modules\Customer\Entities\Taggables;
use Modules\Project\Entities\Project;

/**
 * @OA\Schema(
 * schema="TicketModel",
 * @OA\Xml(name="TicketModel"),
 * @OA\Property(property="id", type="integer", description="Mã ticket hệ thống tự sinh", example=1),
 * @OA\Property(property="admin_replying", type="integer", description="Trạng thái admin đang trả lời", example=1),
 * @OA\Property(property="user_id", type="integer", description="ID người dùng", example=123),
 * @OA\Property(property="contact_id", type="integer", description="ID liên hệ", example=456),
 * @OA\Property(property="email", type="string", format="email", description="Email người dùng", example="Loi@example.com"),
 * @OA\Property(property="name", type="string", description="Tên của người tạo ticket", example="Nguyễn Văn A"),
 * @OA\Property(property="department", type="integer", description="Phòng ban xử lý ticket", example=1),
 * @OA\Property(property="priority", type="integer", description="Mức độ ưu tiên của ticket", example=1),
 * @OA\Property(property="status", type="integer", description="Trạng thái của ticket", example=1),
 * @OA\Property(property="service", type="integer", description="Dịch vụ liên quan đến ticket", example=1),
 * @OA\Property(property="ticketkey", type="string", description="Mã định danh ticket", example="TK123456"),
 * @OA\Property(property="subject", type="string", description="Tiêu đề của ticket", example="Yêu cầu hỗ trợ kỹ thuật"),
 * @OA\Property(property="message", type="string", description="Nội dung của ticket", example="Máy chủ bị lỗi không truy cập được."),
 * @OA\Property(property="admin", type="integer", description="ID của admin xử lý ticket", example=5),
 * @OA\Property(property="date", type="string", format="date-time", description="Thời gian tạo ticket", example="2024-12-05"),
 * @OA\Property(property="project_id", type="integer", description="ID dự án liên quan", example=1001),
 * @OA\Property(property="last_reply", type="string", format="date-time", description="Thời gian trả lời cuối cùng", example="2024-12-06"),
 * @OA\Property(property="client_read", type="integer", description="Khách hàng đã đọc chưa", example=1),
 * @OA\Property(property="admin_read", type="integer", description="Admin đã đọc chưa", example=1),
 * @OA\Property(property="assigned", type="integer", description="ID admin được giao xử lý", example=10),
 * @OA\Property(
 *     property="tags",
 *     type="array",
 *     description="Danh sách các tag liên kết với ticket (có thể để trống)",
 *     @OA\Items(
 *         type="object",
 *         @OA\Property(property="id", type="integer", description="ID của tag", example=1, minimum=1),
 *         @OA\Property(property="name", type="string", description="Tên của tag", example="Bug", maxLength=100),
 *         @OA\Property(property="tag_order", type="integer", description="Thứ tự của tag", example=1, minimum=1)
 *     ),
 *     nullable=true
 * ),
 *     @OA\Property(property="customFields", type="array", @OA\Items(ref="#/components/schemas/CustomFieldModel")),
 *     @OA\Property(property="customFieldsValues", type="array", @OA\Items(ref="#/components/schemas/CustomFieldValueModel")),
 * )
 */
class Ticket extends BaseModel
{
    protected $table = 'tickets';

    protected $primaryKey = 'id';

    protected $fillable = [
        'admin_replying',
        'user_id',
        'contact_id',
        'email',
        'name',
        'department',
        'priority',
        'status',
        'service',
        'ticketkey',
        'subject',
        'message',
        'admin',
        'date',
        'project_id',
        'last_reply',
        'client_read',
        'admin_read',
        'assigned'
    ];

    protected $hidden = [
        'updated_at',
        'updated_by',
        'created_at',
        'created_by'
    ];

    public $timestamps = true;

    protected $appends = ['customFieldsValues', 'customFields'];

    public function getCustomFieldsValuesAttribute()
    {
        return $this->customFieldsValues()->get();
    }

    public function getCustomFieldsAttribute()
    {
        return $this->customFields()->get();
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, Taggables::class, 'rel_id', 'tag_id')->where('rel_type', '=', 'ticket');
    }

    public function taggable()
    {
        return $this->hasMany(Taggables::class, 'rel_id', 'id');
    }

    public function departments()
    {
        return $this->belongsTo(Department::class, 'department');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'assigned');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    /**
     *Get customfieldsvalues hasMany to ticket
     */
    public function customFieldsValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_id')->where('customfieldsvalues.field_to', '=', 'tickets');
    }
    /**
     *Get customfields belongsToMany to ticket
     */
    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, CustomFieldValue::class, 'rel_id', 'field_id')->where('customfieldsvalues.field_to', '=', 'tickets');
    }

    public function services()
    {
        return $this->belongsTo(Service::class, 'service');
    }

    public function file()
    {
        return $this->hasMany(TicketsAttachments::class, 'ticket_id');
    }

    public function ticketPriority()
    {
        return $this->belongsTo(TicketsPriorities::class, 'priority');
    }
}
