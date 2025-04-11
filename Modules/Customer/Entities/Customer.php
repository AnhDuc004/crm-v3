<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;
use Modules\Admin\Entities\Staff;
use Modules\Inventory\Entities\SalesOrder;
use Modules\Project\Entities\Project;
use Modules\Sale\Entities\CreditNotes;
use Modules\Sale\Entities\Estimate;
use Modules\Sale\Entities\Invoice;
use Modules\Task\Entities\Reminder;

/**
 *
 * @OA\Schema(
 * schema="CustomerModel",
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="company", type="string", description="Tên công ty khách hàng", example="Bhome"),
 * @OA\Property(property="vat", type="string", description="Mã số thuế", example="123"),
 * @OA\Property(property="phone_number", type="string", description="SĐT", example="0925641335"),
 * @OA\Property(property="country", type="integer", description="Quốc gia", example="84"),
 * @OA\Property(property="website", type="string", description="Trang web", example="bhome.ketnoith.com"),
 * @OA\Property(property="address", type="string", description="Địa chỉ", example="Hà Nội"),
 * @OA\Property(property="zip", type="string", description="Mã bưu chính", example="1a2b"),
 * @OA\Property(property="city", type="string", description="Thành phố", example="Hà Nội"),
 * @OA\Property(property="state", type="string", description="Tiểu bang", example="a"),
 * @OA\Property(property="active", type="integer", description="Trạng thái", example="1"),
 * @OA\Property(property="lead_id", type="integer", description="Mã khách hàng tiềm năng", example="1"),
 * @OA\Property(property="billing_street", type="string", description="Tên đường", example="Đường 10"),
 * @OA\Property(property="billing_city", type="string", description="Tên thành phố", example="Hà Nội"),
 * @OA\Property(property="billing_state", type="string", description="Tên tiểu bang", example="Tiểu bang a"),
 * @OA\Property(property="billing_zip", type="string", description="Mã bưu chính", example="1a2b"),
 * @OA\Property(property="billing_country", type="integer", description="Mã quốc gia", example="243"),
 * @OA\Property(property="shipping_street", type="string", description="Tên đường", example="Đường 10"),
 * @OA\Property(property="shipping_city", type="string", description="Tên thành phố", example="Hà Nội"),
 * @OA\Property(property="shipping_state", type="string", description="Tên tiểu bang", example="Tiểu bang a"),
 * @OA\Property(property="shipping_zip", type="string", description="Mã bưu chính", example="1a2b"),
 * @OA\Property(property="shipping_country", type="integer", description="Mã quốc gia", example="243"),
 * @OA\Property(property="default_language", type="integer", description="Mã ngôn ngữ", example="1"),
 * @OA\Property(property="default_currency", type="integer", description="Mã tiền tệ", example="1"),
 * @OA\Property(property="show_primary_contact", type="integer", description="Có hiển thị liên hệ không", example="1"),
 * @OA\Property(property="longitude", type="string", description="Kinh độ", example="39,3°B 76,6°T"),
 * @OA\Property(property="latitude", type="string", description="Vĩ độ", example="39,3°"),
 * @OA\Property(property="stripe_id", type="string", description="Mã vạch", example="1hsxa"),
 * @OA\Property(property="registration_confirmed", type="integer", description="Đăng ký_xác_nhận", example="1"),
 * @OA\Property(
 *     property="groups",
 *     type="array",
 *     @OA\Items(
 *         @OA\Property(property="id", type="integer", description="Tag ID"),
 *         @OA\Property(property="name", type="string", description="Tag name", example="High Priority"),
 *        ),
 *        description="List of groups"
 *     ),
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
 *
 * )
 *
 * Class Customer
 *
 */



class Customer extends BaseModel
{
    protected $table = "customers";
    protected $primaryKey = 'id';
    protected $fillable = [
        'company',
        'vat',
        'phone_number',
        'country',
        'zip',
        'city',
        'state',
        'address',
        'website',
        'active',
        'lead_id',
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
        'default_language',
        'default_currency',
        'show_primary_contact'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'longitude',
        'latitude',
        'stripe_id',
        'registration_confirmed',
        'added_from',
        'pivot'
    ];
    public $timestamps = true;

    protected $appends = ['customFieldsValues'];

    public function getCustomFieldsValuesAttribute()
    {
        return $this->customFieldsValues()->get();
    }

    /**
     *  Get customer_groups belongsToMany to customer
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, CustomerGroups::class, 'customer_id', 'group_id');
    }
    /**
     *  Get contacts hasMany to customer
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class, 'customer_id');
    }
    /**
     *  Get estimates hasMany to customer
     */
    public function estimates()
    {
        return $this->hasMany(Estimate::class, 'customer_id');
    }
    /**
     *  Get staff belongsToMany to customer
     */
    public function staff()
    {
        return $this->belongsToMany(Staff::class, CustomerAdmin::class, 'customer_id', 'staff_id');
    }
    /**
     *  Get customer_admin hasMany to customer
     */
    public function admin()
    {
        return $this->hasMany(CustomerAdmin::class, 'customer_id');
    }

    /**
     *  Get project hasMany to customer
     */
    public function project()
    {
        return $this->hasMany(Project::class, 'customer_id');
    }

    /**
     *  Get customfieldsvalues hasMany to customer
     */
    public function customFieldsValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_id')->where('customfieldsvalues.field_to', '=', 'customers');
    }
    /**
     *  Get customfields belongsToMany to customer
     */
    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, CustomFieldValue::class, 'rel_id', 'field_id')->where('customfieldsvalues.field_to', '=', 'customers');
    }

    /**
     *  Get creditNote hasMany to customer
     */
    public function creditNote()
    {
        return $this->hasMany(CreditNotes::class, 'customer_id');
    }
    /**
     *  Get invoice hasMany to customer
     */
    public function invoice()
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }
}
