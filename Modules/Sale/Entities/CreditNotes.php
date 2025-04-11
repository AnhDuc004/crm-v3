<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Modules\Project\Entities\Project;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;

/**
 * @OA\Schema(
 * schema="CreditNoteModel",
 * @OA\Xml(name="CreditNoteModel"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example=1),
 * @OA\Property(property="customer_id", type="integer", description="Mã khách hàng", example=2),
 * @OA\Property(property="project_id", type="integer", description="Mã dự án", example=37),
 * @OA\Property(property="deleted_customer_name", type="string", description="Tên khách hàng đã xóa", example="John Doe"),
 * @OA\Property(property="number", type="integer", description="Số hóa đơn", example=12345),
 * @OA\Property(property="prefix", type="string", description="Tiền tố hóa đơn", example="CN"),
 * @OA\Property(property="number_format", type="integer", description="Định dạng số hóa đơn", example=12345),
 * @OA\Property(property="date", type="date", description="Ngày hóa đơn", example="2024-12-04"),
 * @OA\Property(property="admin_note", type="string", description="Ghi chú của quản trị viên", example="Ghi chú về hóa đơn"),
 * @OA\Property(property="terms", type="string", description="Điều khoản thanh toán", example="Thanh toán trong 30 ngày"),
 * @OA\Property(property="client_note", type="string", description="Ghi chú khách hàng", example="Chú ý: Phí vận chuyển không bao gồm"),
 * @OA\Property(property="currency", type="integer", description="Mã loại tiền tệ", example=1),
 * @OA\Property(property="subtotal", type="decimal", description="Tổng trước thuế", example=500.00),
 * @OA\Property(property="total_tax", type="decimal", description="Tổng thuế", example=50.00),
 * @OA\Property(property="total", type="decimal", description="Tổng số tiền", example=550.00),
 * @OA\Property(property="adjustment", type="decimal", description="Điều chỉnh", example=10.00),
 * @OA\Property(property="added_from", type="integer", description="Mã nhân viên thêm hóa đơn", example=5),
 * @OA\Property(property="status", type="integer", description="Trạng thái hóa đơn", example=1),
 * @OA\Property(property="discount_percent", type="decimal", description="Phần trăm chiết khấu", example=5.0),
 * @OA\Property(property="discount_total", type="decimal", description="Tổng chiết khấu", example=25.00),
 * @OA\Property(property="discount_type", type="string", description="Loại chiết khấu", example="percentage"),
 * @OA\Property(property="billing_street", type="string", description="Địa chỉ thanh toán (đường)", example="123 Main St"),
 * @OA\Property(property="billing_city", type="string", description="Địa chỉ thanh toán (thành phố)", example="Hanoi"),
 * @OA\Property(property="billing_state", type="string", description="Địa chỉ thanh toán (tỉnh)", example="Hanoi"),
 * @OA\Property(property="billing_zip", type="string", description="Mã bưu điện địa chỉ thanh toán", example="100000"),
 * @OA\Property(property="billing_country", type="integer", description="Địa chỉ thanh toán (quốc gia)", example=1),
 * @OA\Property(property="shipping_street", type="string", description="Địa chỉ giao hàng (đường)", example="456 Secondary St"),
 * @OA\Property(property="shipping_city", type="string", description="Địa chỉ giao hàng (thành phố)", example="Hanoi"),
 * @OA\Property(property="shipping_state", type="string", description="Địa chỉ giao hàng (tỉnh)", example="Hanoi"),
 * @OA\Property(property="shipping_zip", type="string", description="Mã bưu điện địa chỉ giao hàng", example="100000"),
 * @OA\Property(property="shipping_country", type="integer", description="Địa chỉ giao hàng (quốc gia)", example=1),
 * @OA\Property(property="include_shipping", type="tinyInteger", description="Bao gồm chi phí vận chuyển", example=1),
 * @OA\Property(property="show_shipping_on_credit_note", type="tinyInteger", description="Hiển thị chi phí vận chuyển trên hóa đơn tín dụng", example=1),
 * @OA\Property(property="show_quantity_as", type="integer", description="Cách hiển thị số lượng", example=1),
 * @OA\Property(property="reference_no", type="string", description="Số tham chiếu hóa đơn", example="REF12345"),
 * @OA\Property(property="remaining_amount", type="decimal", description="Số tham chiếu hóa đơn", example=100.00),
 * @OA\Property(property="project", ref="#/components/schemas/ProjectModel"),
 *     @OA\Property(property="itemable", type="array", @OA\Items(ref="#/components/schemas/ItemableModel")),
 *     @OA\Property(property="customFieldsValues", type="array", @OA\Items(ref="#/components/schemas/CustomFieldValueModel")),
 * )
 */



class CreditNotes extends BaseModel
{
    protected $table = "credit_notes";
    protected $primaryKey = 'id';
    protected $fillable = [
        'customer_id',
        'project_id',
        'deleted_customer_name',
        'number',
        'prefix',
        'number_format',
        'date',
        'admin_note',
        'terms',
        'client_note',
        'currency',
        'subtotal',
        'total_tax',
        'total',
        'adjustment',
        'added_from',
        'status',
        'discount_percent',
        'discount_total',
        'discount_type',
        'billing_street',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_country',
        'shipping_street',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'shipping_country',
        'include_shipping',
        'show_shipping_on_credit_note',
        'show_quantity_as',
        'reference_no'
    ];
    public $timestamps = true;
    /*
     *  Get customer info
     */
    protected $appends = ['customFieldsValues', 'customFields'];

    public function getCustomFieldsValuesAttribute()
    {
        return $this->customFieldsValues()->get();
    }

    public function getCustomFieldsAttribute()
    {
        return $this->customFields()->get();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    /*
     *  Get project info
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    /*
     *  Get hasMany itemable
     */
    public function itemable()
    {
        return $this->hasMany(Itemable::class, 'rel_id')->where('rel_type', '=', 'creditnotes');
    }
    /*
     *  Get customfieldsvalues hasMany to creditNote
     */
    public function customFieldsValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_id')->where('customfieldsvalues.field_to', '=', 'credit_note');
    }
    /*
     *  Get customfields belongsToMany to creditNote
     */
    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, CustomFieldValue::class, 'rel_id', 'field_id')->where('customfieldsvalues.field_to', '=', 'credit_note');
    }
    /*
     *  Get hasMany creditNotesRefunds
     */
    public function refunds()
    {
        return $this->hasMany(CreditNotesRefunds::class, 'credit_note_id');
    }
}
