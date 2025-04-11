<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;

/**
 * @OA\Schema(
 *     schema="ItemableModel",
 *     @OA\Xml(name="ItemableModel"),
 *     @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 *     @OA\Property(property="description", type="string", description="Mô tả", example="Máy tính"),
 *     @OA\Property(property="long_description", type="string", description="Mô tả dài", example="Thiết bị máy tính"),
 *     @OA\Property(property="rate", type="number", format="double", description="Giá", example="50.00"),
 *     @OA\Property(property="qty", type="integer", description="Số lượng", example="5"),
 *     @OA\Property(property="item_order", type="integer", description="Số lượng đặt", example="5"),
 *     @OA\Property(property="unit", type="string", description="Đơn vị tính", example="VND"),
 *     @OA\Property(property="rel_id", type="integer", description="Mã công việc liên quan", example="1"),
 *     @OA\Property(property="rel_type", type="string", description="Tên công việc liên quan", example="customer"),
 *     @OA\Property(
 *         property="item_tax",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="tax_rate", type="number", format="double", description="Thuế suất", example="10.00"),
 *             @OA\Property(property="tax_amount", type="number", format="double", description="Số tiền thuế", example="10.00"),
 *             @OA\Property(property="tax_name", type="string", description="Tên thuế", example="VAT")
 *         )
 *     )
 * )
 */
class Itemable extends BaseModel
{
    protected $table = "itemable";
    protected $fillable = [
        'id',
        'rel_id',
        'rel_type',
        'description',
        'long_description',
        'qty',
        'rate',
        'unit',
        'item_order'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];
    public $timestamps = true;

    protected $appends = ['customFieldsValues'];

    public function getCustomFieldsValuesAttribute()
    {
        return $this->customFieldsValues()->get();
    }
    
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'rel_id');
    }
    /**
     *  Get customfieldsvalues hasMany to item
     */
    public function customFieldsValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_id')->where('customfieldsvalues.field_to', '=', 'items');
    }
    /**
     *  Get customfields belongsToMany to item
     */
    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, CustomFieldValue::class, 'rel_id', 'field_id')->where('customfieldsvalues.field_to', '=', 'items');
    }

    public function itemTax()
    {
        return $this->hasMany(ItemTax::class, 'item_id', 'id');
    }
}
