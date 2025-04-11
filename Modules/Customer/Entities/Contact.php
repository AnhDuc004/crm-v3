<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * schema="ContactModel",
 * required={"first_name", "last_name", "email", "phone_number", "title", "active"},
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="customer_id", type="integer", description="Mã khách hàng", example="1"),
 * @OA\Property(property="is_primary", type="integer", description="Có liên hệ không (0: false, 1: true)", example="1"),
 * @OA\Property(property="first_name", type="string", description="Tên (độ dài tối đa 191 ký tự)", maxLength=191, example="Lợi"),
 * @OA\Property(property="last_name", type="string", description="Họ (độ dài tối đa 191 ký tự)", maxLength=191, example="Nguyễn"),
 * @OA\Property(property="phone_number", type="string", description="SĐT (độ dài tối đa 100 ký tự)", maxLength=100, example="0925641335"),
 * @OA\Property(property="email", type="string", description="Email (độ dài tối đa 100 ký tự)", maxLength=100, example="nguyensiloi@gmail.com"),
 * @OA\Property(property="title", type="string", description="Tiêu đề (độ dài tối đa 100 ký tự)", maxLength=100, example="Bhome"),
 * @OA\Property(property="active", type="integer", description="Trạng thái hoạt động", example="1"),
 * @OA\Property(property="invoice_emails", type="integer", description="Có quyền trong hóa đơn không (0: false, 1: true)", example="0"),
 * @OA\Property(property="estimate_emails", type="integer", description="Có quyền trong báo giá không (0: false, 1: true)", example="0"),
 * @OA\Property(property="credit_note_emails", type="integer", description="Có quyền trong note không (0: false, 1: true)", example="0"),
 * @OA\Property(property="contract_emails", type="integer", description="Có quyền trong hợp đồng không (0: false, 1: true)", example="0"),
 * @OA\Property(property="task_emails", type="integer", description="Có quyền trong công việc không (0: false, 1: true)", example="0"),
 * @OA\Property(property="project_emails", type="integer", description="Có quyền trong dự án không (0: false, 1: true)", example="0"),
 * @OA\Property(
 *     property="customFieldsValues",
 *     type="array",
 *     description="Danh sách các giá trị của custom fields (có thể để trống)",
 *     @OA\Items(
 *         type="object",
 *         @OA\Property(property="value", type="string", description="Giá trị của custom field", example="LoiNguyen02", maxLength=255)
 *     ),
 *     nullable=true
 * )
 *
 * )
 * )
 * Class Contact
 *
 */

class Contact extends BaseModel
{
    protected $table = "contacts";
    protected $primaryKey = 'id';
    protected $fillable = [
        'customer_id',
        'is_primary',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'title',
        'active',
        'profile_image',
        'invoice_emails',
        'estimate_emails',
        'credit_note_emails',
        'contract_emails',
        'task_emails',
        'project_emails',
        'ticket_emails'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'datecreated',
        'new_pass_key',
        'new_pass_key_requested',
        'email_verified_at',
        'email_verification_key',
        'email_verification_sent_at',
        'last_ip',
        'last_login',
        'last_password_change'
    ];
    public $timestamps = true;

    protected $appends = ['customFieldsValues'];

    public function getCustomFieldsValuesAttribute()
    {
        return $this->customFieldsValues()->get();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    /**
     *  Get customfieldsvalues hasMany to contact
     */
    public function customFieldsValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_id')->where('customfieldsvalues.field_to', '=', 'contacts');
    }
    /**
     *  Get customfields belongsToMany to contact
     */
    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, CustomFieldValue::class, 'rel_id', 'field_id')->where('customfieldsvalues.field_to', '=', 'contacts');
    }
}
