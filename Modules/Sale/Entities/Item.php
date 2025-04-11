<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;

/**
 *
 * @OA\Schema(
 * schema="ItemModel",
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="description", type="string", description="Mô tả", example="Máy tính"),
 * @OA\Property(property="long_description", type="string", description="Mô tả dài", example="Thiết bị máy tính"),
 * @OA\Property(property="rate", type="number", format="double", description="Giá", example="50.00"),
 * @OA\Property(property="tax", type="integer", description="Thuế", example="5"),
 * @OA\Property(property="tax2", type="integer", description="Thuế", example="5"),
 * @OA\Property(property="unit", type="string", description="Đơn vị tính", example="VND"),
 * @OA\Property(property="group_id", type="integer", description="Mã nhóm", example="1"),
 * 
 * @OA\Property(property="customFieldValue", type="array", @OA\Items(ref="#/components/schemas/CustomFieldValueModel")),
 * 
 * )
 *
 * Class Item
 *
 */
class Item extends BaseModel
{
    protected $table = "items";
    protected $fillable = [
        'description',
        'long_description',
        'rate',
        'tax',
        'tax2',
        'unit',
        'group_id'
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
}
