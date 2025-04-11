<?php

namespace Modules\Sale\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Customer\Entities\Tag;
use Modules\Customer\Entities\Taggables;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Lead\Entities\Lead;

/**
 * @OA\Schema(
 *     schema="Proposal",
 *     type="object",
 *     required={"subject", "total", "currency", "status", "date", "created_at", "updated_at"},
 *     @OA\Property(property="id", type="integer", description="ID của proposal", example=1, readOnly=true),
 *     @OA\Property(property="subject", type="string", maxLength=191, description="Chủ đề của proposal", example="Proposal Subject"),
 *     @OA\Property(property="content", type="string", description="Nội dung của proposal", example="Content of the proposal", nullable=true),
 *     @OA\Property(property="addedfrom", type="integer", description="ID người thêm proposal", example=123),
 *     @OA\Property(property="datecreated", type="string", format="date-time", description="Ngày tạo proposal", example="2025-01-13T00:00:00Z"),
 *     @OA\Property(property="total", type="number", format="float", description="Tổng tiền của proposal", example=1000.50),
 *     @OA\Property(property="subtotal", type="number", format="float", description="Tổng tạm tính của proposal", example=950.00, nullable=true),
 *     @OA\Property(property="total_tax", type="number", format="float", description="Thuế của proposal", example=50.00, nullable=true),
 *     @OA\Property(property="adjustment", type="number", format="float", description="Điều chỉnh số tiền", example=5.00, nullable=true),
 *     @OA\Property(property="discount_percent", type="number", format="float", description="Tỷ lệ giảm giá", example=10.00, nullable=true),
 *     @OA\Property(property="discount_total", type="number", format="float", description="Tổng giảm giá", example=100.00, nullable=true),
 *     @OA\Property(property="discount_type", type="string", maxLength=30, description="Loại giảm giá", example="Fixed"),
 *     @OA\Property(property="show_quantity_as", type="integer", description="Hiển thị số lượng dưới dạng gì", example=1),
 *     @OA\Property(property="currency", type="integer", description="Mã tiền tệ", example=840),
 *     @OA\Property(property="open_till", type="string", format="date", description="Ngày hết hạn proposal", example="2025-02-01", nullable=true),
 *     @OA\Property(property="date", type="string", format="date", description="Ngày proposal", example="2025-01-13"),
 *     @OA\Property(property="rel_id", type="integer", description="ID liên quan", example=10, nullable=true),
 *     @OA\Property(property="rel_type", type="string", maxLength=40, description="Loại liên quan", example="Project", nullable=true),
 *     @OA\Property(property="assigned", type="integer", description="ID người được giao", example=11, nullable=true),
 *     @OA\Property(property="hash", type="string", maxLength=32, description="Mã hash của proposal", example="abcd1234", nullable=true),
 *     @OA\Property(property="proposal_to", type="string", maxLength=191, description="Người nhận proposal", example="John Doe", nullable=true),
 *     @OA\Property(property="country", type="integer", description="Mã quốc gia", example=1),
 *     @OA\Property(property="zip", type="string", maxLength=50, description="Mã bưu chính", example="12345", nullable=true),
 *     @OA\Property(property="state", type="string", maxLength=100, description="Tỉnh/Thành phố", example="California", nullable=true),
 *     @OA\Property(property="city", type="string", maxLength=100, description="Thành phố", example="Los Angeles", nullable=true),
 *     @OA\Property(property="address", type="string", maxLength=200, description="Địa chỉ", example="123 Main Street", nullable=true),
 *     @OA\Property(property="email", type="string", maxLength=150, description="Email của người nhận", example="example@example.com", nullable=true),
 *     @OA\Property(property="phone", type="string", maxLength=50, description="Số điện thoại", example="123-456-7890", nullable=true),
 *     @OA\Property(property="allow_comments", type="integer", description="Cho phép bình luận", example=1),
 *     @OA\Property(property="status", type="integer", description="Trạng thái của proposal", example=1),
 *     @OA\Property(property="estimate_id", type="integer", description="ID ước tính", example=123, nullable=true),
 *     @OA\Property(property="invoice_id", type="integer", description="ID hóa đơn", example=456, nullable=true),
 *     @OA\Property(property="date_converted", type="string", format="date-time", description="Ngày chuyển đổi proposal", example="2025-01-13T12:00:00Z", nullable=true),
 *     @OA\Property(property="pipeline_order", type="integer", description="Thứ tự trong pipeline", example=1),
 *     @OA\Property(property="is_expiry_notified", type="integer", description="Thông báo hết hạn", example=0),
 *     @OA\Property(property="acceptance_firstname", type="string", maxLength=50, description="Tên người chấp nhận", example="John", nullable=true),
 *     @OA\Property(property="acceptance_lastname", type="string", maxLength=50, description="Họ người chấp nhận", example="Doe", nullable=true),
 *     @OA\Property(property="acceptance_email", type="string", maxLength=100, description="Email người chấp nhận", example="john.doe@example.com", nullable=true),
 *     @OA\Property(property="acceptance_date", type="string", format="date-time", description="Ngày chấp nhận", example="2025-01-13T12:00:00Z", nullable=true),
 *     @OA\Property(property="acceptance_ip", type="string", maxLength=40, description="IP người chấp nhận", example="192.168.0.1", nullable=true),
 *     @OA\Property(property="signature", type="string", maxLength=40, description="Chữ ký của người chấp nhận", example="abcd1234", nullable=true),
 *     @OA\Property(property="created_by", type="integer", description="ID người tạo", example=1, nullable=true),
 *     @OA\Property(property="updated_by", type="integer", description="ID người cập nhật", example=2, nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Ngày tạo", example="2025-01-13T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Ngày cập nhật", example="2025-01-13T12:00:00Z"),
 * 
 *      @OA\Property(property="tags", type="array", @OA\Items(ref="#/components/schemas/TagModel")),
 *      @OA\Property(property="custom_fields", type="array", @OA\Items(ref="#/components/schemas/CustomFieldModel")),
 *      @OA\Property(property="customFieldsValues", type="array", @OA\Items(ref="#/components/schemas/CustomFieldValueModel")),
 *      @OA\Property(property="itemable",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(property="description", type="string", description="itemable", example="itemable"),
 *             @OA\Property(property="qty", type="decimal", description="quantity", example="1"),
 *             @OA\Property(property="rate", type="decimal", description="rate", example="1")
 *         ),
 *         description="Itemable"
 *     ),
 * )
 * 
 * )
 */

class Proposal extends Model
{
    protected $table = 'proposals';

    protected $fillable = [
        'subject',
        'content',
        'total',
        'subtotal',
        'adjustment',
        'discount_percent',
        'discount_total',
        'discount_type',
        'show_quantity_as',
        'currency',
        'open_till',
        'date',
        'rel_id',
        'rel_type',
        'assigned',
        'proposal_to',
        'country',
        'zip',
        'state',
        'city',
        'address',
        'email',
        'phone',
        'allow_comments',
        'status',
        'estimate_id',
        'invoice_id',
        'created_at'
    ];

    protected $hidden = [
        'updated_at',
        'created_by',
        'updated_by',
        'datecreated',
        'total_tax',
        'date_converted',
        'pipeline_order',
        'is_expiry_notified',
        'acceptance_firstname',
        'acceptance_lastname',
        'acceptance_email',
        'acceptance_date',
        'hash',
        'acceptance_id',
        'signature'

    ];

    public $timestamps = true;

    protected $appends = ['customFieldsValues'];

    public function getCustomFieldsValuesAttribute()
    {
        return $this->customFieldsValues()->get();
    }

    public function leads()
    {
        return $this->belongsTo(Lead::class, 'rel_id', 'id');
    }

    public function proposalComments()
    {
        return $this->hasmany(ProposalComments::class, 'proposal_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, Taggables::class, 'rel_id', 'tag_id')->where('rel_type', '=', 'proposal');
    }

    public function itemable()
    {
        return $this->hasMany(Itemable::class, 'rel_id')->where('rel_type', '=', 'proposal');
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, Itemable::class, 'rel_id', 'item_order');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'rel_id', 'id');
    }
    /**
     *  Get customfieldsvalues hasMany to proposal
     */
    public function customFieldsValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_id')->where('customfieldsvalues.field_to', '=', 'proposal');
    }
    /**
     *  Get customfields belongsToMany to proposal
     */
    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, CustomFieldValue::class, 'rel_id', 'field_id')->where('customfieldsvalues.field_to', '=', 'proposal');
    }

    public function taggable()
    {
        return $this->hasMany(Taggables::class, 'rel_id', 'id');
    }
}
