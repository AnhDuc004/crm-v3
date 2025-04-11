<?php

namespace Modules\Expense\Entities;

use App\Models\BaseModel;
use Modules\Project\Entities\Project;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Customer\Entities\File;
use Modules\Sale\Entities\Invoice;
use Modules\Sale\Entities\PaymentMode;

/**
 * @OA\Schema(
 * schema="ExpensesModel",
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="category", type="integer", description="Danh mục chi phí", example="2"),
 * @OA\Property(property="currency", type="integer", description="Mã loại tiền tệ", example="1"),
 * @OA\Property(property="amount", type="decimal", description="Số tiền", example="33.00"),
 * @OA\Property(property="tax", type="integer", description="Thuế 1", example="10.5"),
 * @OA\Property(property="tax2", type="integer", description="Thuế 2", example="5.0"),
 * @OA\Property(property="reference_no", type="string", description="Số tham chiếu", example="REF001"),
 * @OA\Property(property="note", type="string", description="Ghi chú", example="Chi phí tháng 12"),
 * @OA\Property(property="expense_name", type="string", description="Tên chi phí", example="Chi phí vận chuyển"),
 * @OA\Property(property="customer_id", type="integer", description="Mã khách hàng", example="1"),
 * @OA\Property(property="project_id", type="integer", description="Mã dự án", example="37"),
 * @OA\Property(property="billable", type="integer", description="Chi phí có thể xuất hóa đơn", example=1),
 * @OA\Property(property="invoice_id", type="integer", description="Mã hóa đơn", example="1001"),
 * @OA\Property(property="payment_mode", type="string", description="Phương thức thanh toán", example="online"),
 * @OA\Property(property="date", type="date", description="Ngày chi phí", example="2024-12-04"),
 * @OA\Property(property="recurring_type", type="string", description="Loại chi phí định kỳ", example="month"),
 * @OA\Property(property="repeat_every", type="integer", description="Lặp lại sau số đơn vị thời gian", example="1"),
 * @OA\Property(property="recurring", type="integer", description="Chi phí định kỳ", example=1),
 * @OA\Property(property="cycles", type="integer", description="Chu kỳ định kỳ", example="12"),
 * @OA\Property(property="total_cycles", type="integer", description="Tổng số chu kỳ đã thực hiện", example="5"),
 * @OA\Property(property="custom_recurring", type="integer", description="Tùy chỉnh định kỳ", example=1),
 * @OA\Property(property="last_recurring_date", type="date", description="Ngày định kỳ cuối cùng", example="2024-11-30"),
 * @OA\Property(property="create_invoice_billable", type="tinyInteger", description="Tạo hóa đơn cho chi phí có thể xuất hóa đơn", example=1),
 * @OA\Property(property="send_invoice_to_customer", type="tinyInteger", description="Gửi hóa đơn cho khách hàng", example=0),
 * @OA\Property(property="recurring_from", type="integer", description="Bắt đầu từ mã định kỳ", example="1"),
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
 */

class Expenses extends BaseModel
{
    protected $table = 'expenses';

    protected $fillable = [
        'category',
        'currency',
        'amount',
        'tax',
        'tax2',
        'reference_no',
        'note',
        'expense_name',
        'customer_id',
        'project_id',
        'billable',
        'invoice_id',
        'payment_mode',
        'date',
        'recurring_type',
        'repeat_every',
        'recurring',
        'cycles',
        'total_cycles',
        'custom_recurring',
        'last_recurring_date',
        'create_invoice_billable',
        'send_invoice_to_customer',
        'recurring_from',
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

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function paymentMode()
    {
        return $this->belongsTo(PaymentMode::class, 'payment_mode');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function expenseCategory()
    {
        return $this->belongsTo(ExpensesCategories::class, 'category');
    }

    public function customFieldsValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_id')
            ->where('customfieldsvalues.field_to', 'expenses');
    }

    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, CustomFieldValue::class, 'rel_id', 'field_id')
            ->where('customfieldsvalues.field_to', 'expenses');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'rel_id')->where('rel_type', 'expense');
    }
}
