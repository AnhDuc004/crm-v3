<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;
use Modules\Project\Entities\Project;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Customer\Entities\Tag;
use Modules\Customer\Entities\Taggables;

/**
 * @OA\Schema(
 *     schema="InvoiceModel",
 *     type="object",
 *     required={"id", "sent", "customer_id", "number", "date", "subtotal", "total_tax", "total", "hash", "sale_agent"},
 *     description="Invoice schema",
 *     @OA\Property(property="id", type="integer", description="Unique identifier of the invoice"),
 *     @OA\Property(property="sent", type="boolean", description="Whether the invoice has been sent"),
 *     @OA\Property(property="date_send", type="string", format="date-time", description="Date the invoice was sent"),
 *     @OA\Property(property="customer_id", type="integer", description="Customer ID associated with the invoice"),
 *     @OA\Property(property="deleted_customer_name", type="string", maxLength=100, nullable=true, description="Name of the customer if deleted"),
 *     @OA\Property(property="number", type="integer", description="Invoice number"),
 *     @OA\Property(property="prefix", type="string", maxLength=50, nullable=true, description="Invoice prefix"),
 *     @OA\Property(property="number_format", type="integer", default=0, description="Invoice number format"),
 *     @OA\Property(property="date", type="string", format="date", description="Invoice creation date"),
 *     @OA\Property(property="due_date", type="string", format="date", nullable=true, description="Due date for the invoice"),
 *     @OA\Property(property="currency", type="integer", description="Currency type"),
 *     @OA\Property(property="subtotal", type="number", format="float", description="Subtotal amount of the invoice"),
 *     @OA\Property(property="total_tax", type="number", format="float", default=0.00, description="Total tax amount"),
 *     @OA\Property(property="total", type="number", format="float", description="Total amount of the invoice"),
 *     @OA\Property(property="adjustment", type="number", format="float", nullable=true, description="Adjustment amount"),
 *     @OA\Property(property="hash", type="string", maxLength=32, description="Hash for invoice security"),
 *     @OA\Property(property="status", type="integer", default=1, description="Invoice status"),
 *     @OA\Property(property="client_note", type="string", nullable=true, description="Note for the client"),
 *     @OA\Property(property="admin_note", type="string", nullable=true, description="Note for administrators"),
 *     @OA\Property(property="last_overdue_reminder", type="string", format="date", nullable=true, description="Date of the last overdue reminder"),
 *     @OA\Property(property="cancel_overdue_reminders", type="integer", default=0, description="Whether overdue reminders are cancelled"),
 *     @OA\Property(property="allowed_payment_modes", type="string", nullable=true, description="Allowed payment modes"),
 *     @OA\Property(property="token", type="string", nullable=true, description="Token for the invoice"),
 *     @OA\Property(property="discount_percent", type="number", format="float", default=0.00, description="Discount percentage"),
 *     @OA\Property(property="discount_total", type="number", format="float", default=0.00, description="Total discount amount"),
 *     @OA\Property(property="discount_type", type="string", maxLength=30, nullable=true, description="Type of discount"),
 *     @OA\Property(property="recurring", type="integer", default=0, description="Whether the invoice is recurring"),
 *     @OA\Property(property="recurring_type", type="string", maxLength=10, nullable=true, description="Recurring type"),
 *     @OA\Property(property="custom_recurring", type="boolean", default=0, description="Custom recurring option"),
 *     @OA\Property(property="cycles", type="integer", default=0, description="Number of cycles"),
 *     @OA\Property(property="total_cycles", type="integer", default=0, description="Total number of cycles"),
 *     @OA\Property(property="is_recurring_from", type="integer", nullable=true, description="Recurring from another invoice ID"),
 *     @OA\Property(property="last_recurring_date", type="string", format="date", nullable=true, description="Last recurring date"),
 *     @OA\Property(property="terms", type="string", nullable=true, description="Terms and conditions"),
 *     @OA\Property(property="sale_agent", type="integer", default=0, description="Sales agent ID"),
 *     @OA\Property(property="billing_street", type="string", maxLength=200, nullable=true, description="Billing street address"),
 *     @OA\Property(property="billing_city", type="string", maxLength=100, nullable=true, description="Billing city"),
 *     @OA\Property(property="billing_state", type="string", maxLength=100, nullable=true, description="Billing state"),
 *     @OA\Property(property="billing_zip", type="string", maxLength=100, nullable=true, description="Billing ZIP code"),
 *     @OA\Property(property="billing_country", type="integer", nullable=true, description="Billing country ID"),
 *     @OA\Property(property="shipping_street", type="string", maxLength=200, nullable=true, description="Shipping street address"),
 *     @OA\Property(property="shipping_city", type="string", maxLength=100, nullable=true, description="Shipping city"),
 *     @OA\Property(property="shipping_state", type="string", maxLength=100, nullable=true, description="Shipping state"),
 *     @OA\Property(property="shipping_zip", type="string", maxLength=100, nullable=true, description="Shipping ZIP code"),
 *     @OA\Property(property="shipping_country", type="integer", nullable=true, description="Shipping country ID"),
 *     @OA\Property(property="include_shipping", type="boolean", description="Whether to include shipping in the invoice"),
 *     @OA\Property(property="show_shipping_on_invoice", type="boolean", default=1, description="Show shipping details on invoice"),
 *     @OA\Property(property="show_quantity_as", type="integer", default=1, description="Display style for quantity"),
 *     @OA\Property(property="project_id", type="integer", nullable=true, description="Associated project ID"),
 *     @OA\Property(property="subscription_id", type="integer", default=0, description="Associated subscription ID"),
 * 
 *     @OA\Property(property="tags", type="array", @OA\Items(ref="#/components/schemas/TagModel")),
 *     @OA\Property(property="itemable", type="array", @OA\Items(ref="#/components/schemas/ItemableModel")),
 *     @OA\Property(property="customFieldsValues", type="array", @OA\Items(ref="#/components/schemas/CustomFieldValueModel")),
 *     @OA\Property(property="customFields", type="array", @OA\Items(ref="#/components/schemas/CustomFieldModel"))
 * )
 */

class Invoice extends BaseModel
{
    protected $table = 'invoices';

    protected $primaryKey = 'id';

    protected $fillable = [
        'customer_id',
        'number',
        'prefix',
        'date',
        'due_date',
        'currency',
        'subtotal',
        'total_tax',
        'total',
        'adjustment',
        'status',
        'client_note',
        'discount_percent',
        'discount_total',
        'discount_type',
        'recurring_type',
        'total_cycles',
        'terms',
        'sale_agent',
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
        'show_shipping_on_voice',
        'show_quantity_as',
        'project_id',
        'subscription_id',
        'admin_note',
        'recurring',
        'allowed_payment_modes',
        'sent',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'created_by',
        'updated_by',
        'date_send',
        'deleted_customer_name',
        'number_format',
        'datecreated',
        'addedfrom',
        'hash',
        'last_overdue_reminder',
        'cancel_overdue_reminder',
        'token',
        'custom_recurring',
        'cycles',
        'is_recurring_from',
        'last_recurring_date',
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

    public function record()
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class, Taggables::class, 'rel_id', 'tag_id')->where('rel_type', '=', 'invoice');
    }
    public function itemable()
    {
        return $this->hasMany(Itemable::class, 'rel_id')->where('rel_type', '=', 'invoice');
    }
    public function taggable()
    {
        return $this->hasMany(Taggables::class, 'rel_id', 'id');
    }
    /**
     *  Get customfieldsvalues hasMany to invoice
     */
    public function customFieldsValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_id')->where('customfieldsvalues.field_to', '=', 'invoice');
    }
    /**
     *  Get customfields belongsToMany to invoice
     */
    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, CustomFieldValue::class, 'rel_id', 'field_id')->where('customfieldsvalues.field_to', '=', 'invoice');
    }
}
