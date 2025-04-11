<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;
use Modules\Admin\Entities\Staff;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Customer\Entities\Tag;
use Modules\Customer\Entities\Taggables;
use Modules\Project\Entities\Project;

/**
 * @OA\Schema(
 *     schema="EstimateModel",
 *     required={"sale_agent"},
 *     @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 *     @OA\Property(property="customer_id", type="integer", description="Mã khách hàng", example="1"),
 *     @OA\Property(property="project_id", type="integer", description="Mã dự án", example="1"),
 *     @OA\Property(property="number", type="integer", description="Số báo giá", example="00001"),
 *     @OA\Property(property="prefix", type="string", description="Kí hiệu báo giá", example="EST-"),
 *     @OA\Property(property="currency", type="number", format="double", description="Đơn vị tính", example="1"),
 *     @OA\Property(property="subtotal", type="number", format="double", description="Chi phí chưa qua thuế", example="500.00"),
 *     @OA\Property(property="date", type="date", description="Ngày báo giá", example="2020/10/10"),
 *     @OA\Property(property="expirydate", type="date", description="Ngày hết hạn", example="2020/10/20"),
 *     @OA\Property(property="total", type="number", format="double", description="Thành tiền", example="500.00"),
 *     @OA\Property(property="adjustment", type="number", format="double", description="Điều chỉnh giá", example="30.00"),
 *     @OA\Property(property="status", type="integer", description="Trạng thái", example="1"),
 *     @OA\Property(property="client_note", type="string", description="Ghi chú khách hàng", example="chưa sử dụng"),
 *     @OA\Property(property="discount_percent", type="number", format="double", description="Chiết khấu", example="50.00"),
 *     @OA\Property(property="discount_total", type="number", format="double", description="Tổng chiết khấu", example="50.00"),
 *     @OA\Property(property="discount_type", type="string", description="Loại chiết khấu", example="before_tax"),
 *     @OA\Property(property="invoice_id", type="integer", description="Mã hóa đơn", example="1"),
 *     @OA\Property(property="invoice_date", type="date", description="Ngày tạo hóa đơn", example="2020/10/20"),
 *     @OA\Property(property="sale_agent", type="integer", description="Mã người bán", example="1"),
 *     @OA\Property(property="billing_street", type="string", description="Tên đường", example="Đường 10"),
 *     @OA\Property(property="billing_city", type="string", description="Tên thành phố", example="Hà Nội"),
 *     @OA\Property(property="billing_state", type="string", description="Tên tiểu bang", example="Tiểu bang a"),
 *     @OA\Property(property="billing_zip", type="string", description="Mã bưu chính", example="1a2b"),
 *     @OA\Property(property="billing_country", type="integer", description="Mã quốc gia", example="243"),
 *     @OA\Property(property="shipping_street", type="string", description="Tên đường", example="Đường 10"),
 *     @OA\Property(property="shipping_city", type="string", description="Tên thành phố", example="Hà Nội"),
 *     @OA\Property(property="shipping_state", type="string", description="Tên tiểu bang", example="Tiểu bang a"),
 *     @OA\Property(property="shipping_zip", type="string", description="Mã bưu chính", example="1a2b"),
 *     @OA\Property(property="shipping_country", type="integer", description="Mã quốc gia", example="243"),
 *     @OA\Property(property="include_shipping", type="integer", description="Địa chỉ đặt hàng", example="0"),
 *     @OA\Property(property="show_shipping_on_estimate", type="integer", description="Có phí vận chuyển trong báo giá không", example="0"),
 *     @OA\Property(property="reference_no", type="string", description="Thao khảo", example="abc"),
 *     @OA\Property(property="show_quantity_as", type="integer", description="Hiển thị số lượng dưới dạng", example="1"),
 *     @OA\Property(property="terms", type="string", description="Điều khoản và luật lệ", example="Điều 1"),
 *
 *     @OA\Property(property="customer", ref="#/components/schemas/CustomerModel"),
 *     @OA\Property(property="project", ref="#/components/schemas/ProjectModel"),
 *     @OA\Property(property="tags", type="array", @OA\Items(ref="#/components/schemas/TagModel")),
 *     @OA\Property(property="itemable", type="array", @OA\Items(ref="#/components/schemas/ItemableModel")),
 *     @OA\Property(property="customFieldsValues", type="array", @OA\Items(ref="#/components/schemas/CustomFieldValueModel")),
 *     @OA\Property(property="customFields", type="array", @OA\Items(ref="#/components/schemas/CustomFieldModel"))
 * )
 */
class Estimate extends BaseModel
{
    protected $table = 'estimates';
    protected $primaryKey = 'id';
    protected $fillable = ['customer_id','reference_no', 'project_id', 'number', 'prefix', 'date', 'expiry_date', 'currency', 'subtotal', 'terms', 'total', 'adjustment', 'status', 'clientnote', 'discount_percent', 'discount_total', 'discount_type', 'invoiceid', 'invoiced_date', 'sale_agent', 'billing_street', 'billing_city', 'billing_state', 'billing_zip', 'billing_country', 'shipping_street', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country', 'include_shipping', 'show_shipping_on_estimate', 'show_quantity_as', 'addedfrom', 'adminnote', 'number_format', 'hash', 'datecreated', 'total_tax', 'sent'];

    protected $hidden = ['created_at', 'updated_at', 'created_by', 'updated_by', 'datesend', 'deleted_customer_name', 'pipeline_order', 'is_expiry_notified', 'acceptance_lastname', 'acceptance_email', 'acceptance_date', 'acceptance_ip', 'signature'];

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

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function itemable()
    {
        return $this->hasMany(Itemable::class, 'rel_id')->where('rel_type', 'estimate');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, Taggables::class, 'rel_id', 'tag_id')->where('rel_type', '=', 'estimate');
    }

    public function taggable()
    {
        return $this->hasMany(Taggables::class, 'rel_id', 'id');
    }
    /**
     *  Get customfieldsvalues hasMany to estimate
     */
    public function customFieldsValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_id')->where('customfieldsvalues.field_to', '=', 'estimate');
    }
    /**
     *  Get customfields belongsToMany to estimate
     */
    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, CustomFieldValue::class, 'rel_id', 'field_id')->where('customfieldsvalues.field_to', '=', 'estimate');
    }

    public function saleAgent()
    {
        return $this->belongsTo(Staff::class, 'sale_agent');
    }
}
