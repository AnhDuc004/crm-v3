<?php

namespace Modules\Lead\Entities;

use App\Models\BaseModel;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Task\Entities\Task;
use Modules\Admin\Entities\Staff;
use Modules\Customer\Entities\Tag;
use Modules\Customer\Entities\Taggables;
use Modules\Customer\Entities\Note;
use Modules\Sale\Entities\Proposal;
use Modules\Task\Entities\Reminder;

/**
 * @OA\Schema(
 *     schema="LeadModel",
 *     @OA\Property(property="name", type="string", description="Tên khách hàng", example="Anh"),
 *     @OA\Property(property="company", type="string", description="Tên công ty", example="THC"),
 *     @OA\Property(property="title", type="string", description="Chức danh", example="Giám đốc"),
 *     @OA\Property(property="description", type="string", description="Mô tả", example="Thanh Hà"),
 *     @OA\Property(property="country", type="integer", description="Quốc gia", example="1"),
 *     @OA\Property(property="zip", type="string", description="Mã bưu chính", example="1a2b"),
 *     @OA\Property(property="city", type="string", description="Thành phố", example="Hà Nội"),
 *     @OA\Property(property="state", type="string", description="Tiểu bang", example="a"),
 *     @OA\Property(property="address", type="string", description="Địa chỉ", example="Hà Nội"),
 *     @OA\Property(property="email", type="string", description="Email", example="abc@gmail.com"),
 *     @OA\Property(property="website", type="string", description="Website", example="bhome.ketnoith.com"),
 *     @OA\Property(property="phone_number", type="string", description="Số điện thoại", example="0925641335"),
 *     @OA\Property(property="is_public", type="integer", description="Có hiển thị không (1: Có, 0: Không)", example="1"),
 *     @OA\Property(property="assigned", type="integer", description="Mã người tạo", example="1"),
 *     @OA\Property(property="date_assigned", type="string", format="date", description="Ngày được phân công", example="2020-10-01"),
 *     @OA\Property(property="lead_order", type="integer", description="Thứ tự lead", example="1"),
 *     @OA\Property(property="status", type="integer", description="Trạng thái (1: Khách hàng, 2: Lead, ...)", example="1"),
 *     @OA\Property(property="source", type="integer", description="Nguồn (1: Facebook, 2: Zalo, ...)", example="1"),
 *     @OA\Property(property="lastest_contact", type="string", format="date", description="Ngày liên hệ gần nhất", example="2020-12-15"),
 *     @OA\Property(property="lastest_lead_status", type="string", description="Trạng thái lead gần nhất", example="1"),
 *     @OA\Property(property="lastest_status_change", type="string", format="date", description="Ngày thay đổi trạng thái gần nhất", example="2020-12-15"),
 *     @OA\Property(property="customer_id", type="integer", description="Mã khách hàng", example="1"),
 *     @OA\Property(property="date_converted", type="string", format="date", description="Ngày chuyển đổi thành khách hàng", example="2020-12-15"),
 *
 *     @OA\Property(property="lead_status", type="object", ref="#/components/schemas/LeadStatus"),
 *     @OA\Property(property="lead_source", type="object", ref="#/components/schemas/LeadSourceModel"),
 *     @OA\Property(property="proposals", type="array", @OA\Items(ref="#/components/schemas/Proposal")),
 *     @OA\Property(property="customer", type="object", ref="#/components/schemas/CustomerModel"),
 *     @OA\Property(property="notes", type="array", @OA\Items(ref="#/components/schemas/NoteModel")),
 *     @OA\Property(property="tags", type="array", @OA\Items(ref="#/components/schemas/TagModel")),
 *     @OA\Property(property="contacts", type="array", @OA\Items(ref="#/components/schemas/ContactModel")),
 *     @OA\Property(property="customFields", type="array", @OA\Items(ref="#/components/schemas/CustomFieldModel")),
 *     @OA\Property(property="customFieldValues", type="array", @OA\Items(ref="#/components/schemas/CustomFieldValueModel")),
 * )
 */

class Lead extends BaseModel
{
    protected $table = "leads";

    protected $fillable = [
        'name',                // Tên khách hàng
        'company',             // Tên công ty
        'title',               // Chức danh
        'description',         // Mô tả
        'country',             // Quốc gia
        'zip',                 // Mã bưu chính
        'city',                // Thành phố
        'state',               // Tiểu bang
        'address',             // Địa chỉ
        'email',               // Email
        'website',             // Website
        'phone_number',        // Số điện thoại
        'is_public',           // Hiển thị hay không
        'default_language',    // Ngôn ngữ mặc định
        'assigned',            // Người tạo
        'date_assigned',       // Ngày phân công
        'lead_order',          // Thứ tự lead
        'status',              // Trạng thái lead
        'source',              // Nguồn lead
        'lastest_contact',     // Ngày liên hệ gần nhất
        'lastest_lead_status', // Trạng thái lead gần nhất
        'lastest_status_change', // Ngày thay đổi trạng thái gần nhất
        'customer_id',         // Mã khách hàng
        'date_converted',      // Ngày chuyển đổi thành khách hàng
    ];

    protected $hidden = [
        'created_at',          // Ngày tạo
        'updated_at',          // Ngày cập nhật
        'created_by',          // Người tạo
        'updated_by',          // Người cập nhật
        'hash',                // Mã băm
        'from_form_id',        // ID form nguồn
        'lost',                // Trạng thái mất
        'junk',                // Trạng thái rác
        'is_imported_from_email_integration', // Từ tích hợp email hay không
        'email_integration_uid', // UID tích hợp email
    ];

    public $timestamps = true;
    /**
     * Lấy trạng thái của lead
     */
    public function leadStatus()
    {
        return $this->belongsTo(LeadStatus::class, 'status');
    }

    /**
     * Lấy nguồn của lead
     */
    public function leadSource()
    {
        return $this->belongsTo(LeadSource::class, 'source');
    }

    /**
     * Lấy nhân viên được phân công
     */
    public function assigned()
    {
        return $this->belongsTo(Staff::class, 'assigned', 'id');
    }

    /**
     * Lấy các đề xuất liên quan đến lead
     */
    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'rel_id');
    }

    /**
     * Lấy các công việc (task) liên quan đến lead
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'rel_id');
    }

    /**
     * Lấy các nhắc nhở (reminder) liên quan đến lead
     */
    public function reminders()
    {
        return $this->hasMany(Reminder::class, 'rel_id');
    }

    /**
     * Lấy khách hàng tương ứng với lead
     */
    public function customer()
    {
        return $this->hasOne(Customer::class, 'lead_id');
    }

    /**
     * Lấy các ghi chú liên quan đến lead
     */
    public function notes()
    {
        return $this->hasMany(Note::class, 'rel_id');
    }

    /**
     * Lấy nhật ký hoạt động (activity log) liên quan đến lead
     */
    public function leadActivityLog()
    {
        return $this->hasMany(LeadActivityLog::class, 'lead_id');
    }

    /**
     * Lấy các tag liên quan đến lead
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, Taggables::class, 'rel_id', 'tag_id')->where('rel_type', '=', 'lead');
    }

    /**
     * Lấy các giá trị trường tùy chỉnh liên quan đến lead
     */
    public function customFieldsValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_id')->where('field_to', '=', 'leads');
    }

    /**
     * Lấy các trường tùy chỉnh (custom fields) liên quan đến lead
     */
    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, CustomFieldValue::class, 'rel_id', 'field_id')->where('customfieldsvalues.field_to', '=', 'leads');
    }

    /**
     * Lấy các taggable liên quan đến lead
     */
    public function taggable()
    {
        return $this->hasMany(Taggables::class, 'rel_id', 'id');
    }
}
