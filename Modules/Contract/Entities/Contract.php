<?php

namespace Modules\Contract\Entities;

use App\Models\BaseModel;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Admin\Entities\Staff;

/**
 *
 * @OA\Schema(
 * schema="ContractModel",
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="customer_id", type="integer", description="Id khách hàng", example="1"),
 * @OA\Property(property="content", type="string", description="Nội dung", example="quần áo"),
 * @OA\Property(property="description", type="string", description="Mô tả", example="sách hay"),
 * @OA\Property(property="subject", type="string", description="Chủ đề", example="Bhome"),
 * @OA\Property(property="client", type="integer", description="Mã khách hàng", example="1"),
 * @OA\Property(property="date_start", type="date", description="Ngày bắt đầu", example="2020-10-10"),
 * @OA\Property(property="date_end", type="date", description="Ngày kết thúc", example="2020-12-12"),
 * @OA\Property(property="contract_type", type="integer", description="Loại hợp đồng", example="1"),
 * @OA\Property(property="is_expiry_notified", type="integer", description="Thông báo hết hạn", example="0"),
 * @OA\Property(property="contract_value", type="number", format="float", description="Giá trị hợp đồng", example="1000.00"),
 * @OA\Property(property="trash", type="integer", description="trạng thái softDelete (0.false, 1.true)", example="0"),
 * @OA\Property(property="not_visible_to_client", type="integer", description="Không hiển thị cho khách hàng (0.false, 1.true)", example="0"),
 * @OA\Property(property="hash", type="string", description="Băm", example="hhhdbsd"),
 * @OA\Property(property="signed", type="integer", description="Đã đăng ký", example="0"),
 * @OA\Property(property="signature", type="string", description="Chữ ký", example="loi"),
 * @OA\Property(property="marked_as_signed", type="integer", description="Đã đánh dấu là đã ký", example="0"),
 * @OA\Property(property="acceptance_firstname", type="string", description="Chấp nhận first name", example="accept"),
 * @OA\Property(property="acceptance_lastname", type="string", description="Chấp nhận last name", example="accept"),
 * @OA\Property(property="acceptance_email", type="string", description="Chấp nhận email", example="accept"),
 * @OA\Property(property="acceptance_date", type="dateTime", description="Ngày chấp nhận", example="2024-10-23"),
 * @OA\Property(property="acceptance_ip", type="string", description="Chấp nhận IP", example="accept"),
 * @OA\Property(property="created_by", type="integer", description="Người tạo", example="1"),
 * @OA\Property(property="updated_by", type="integer", description="Người cập nhật", example="2"),
 * @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo", example="2024-01-01T00:00:00Z"),
 * @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật", example="2024-01-02T00:00:00Z"),
 * @OA\Property(
 *     property="customFieldsValues",
 *     type="array",
 *     description="Danh sách các giá trị của custom fields (có thể để trống)",
 *     @OA\Items(
 *         type="object",
 *         @OA\Property(property="value", type="string", description="Giá trị của custom field", example="Critical Issue", maxLength=255)
 *     ),
 *     nullable=true
 * )
 * )
 *
 * Class Contract
 *
 */
class Contract extends BaseModel
{
    protected $table = "contracts";
    protected $primaryKey = 'id';
    protected $fillable = [
        'content',
        'description',
        'subject',
        'customer_id',
        'contract_type',
        'contract_value',
        'date_start',
        'date_end',
        'trash',
        'not_visible_to_client'
    ];
    protected $hidden = [
        'is_expiry_notified',
        'hash',
        'signed',
        'signature',
        'marked_as_signed',
        'acceptance_firstname',
        'acceptance_lastname',
        'acceptance_email',
        'acceptance_date',
        'acceptance_ip'

    ];
    public $timestamps = true;

    protected $appends = ['customFieldsValues'];

    public function getCustomFieldsValuesAttribute()
    {
        return $this->customFieldsValues()->get();
    }
    /**
     *  Get customer belongTo to contracts
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    /**
     *  Get type belongTo to contracts
     */
    public function type()
    {
        return $this->belongsTo(ContractType::class, 'contract_type');
    }
    /**
     *  Get comment hasMany to contracts
     */

    public function comment()
    {
        return $this->hasMany(ContractComments::class, 'contract_id');
    }

    /**
     *  Get staff belongsToMany to contracts
     */
    public function staff()
    {
        return $this->belongsToMany(Staff::class, ContractComments::class, 'contract_id', 'staff_id');
    }


    /**
     *  Get customfieldsvalues hasMany to contracts
     */
    public function customFieldsValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_id')->where('customfieldsvalues.field_to', '=', 'contracts');
    }
    /**
     *  Get customfields belongsToMany to contracts
     */
    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, CustomFieldValue::class, 'rel_id', 'field_id')->where('customfieldsvalues.field_to', '=', 'contracts');
    }
    /**
     *  Get renewals hasMany to contracts
     */
    public function renewals()
    {
        return $this->hasMany(ContractRenewals::class, 'contract_id');
    }
}
