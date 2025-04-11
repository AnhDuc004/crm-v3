<?php

namespace Modules\Tik\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TikProductSalesAttribute",
 *     title="TikProductSalesAttribute",
 *     description="Product sales attribute model representing product sales attributes",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID thuộc tính bán hàng", example=1),
 *     @OA\Property(property="product_id", type="integer", format="int64", description="ID sản phẩm liên kết với bảng tik_products", example=1),
 *     @OA\Property(property="attribute_id", type="integer", format="int64", description="ID thuộc tính liên kết với bảng tik_attributes", example=1),
 *     @OA\Property(property="value_id", type="integer", format="int64", description="ID giá trị thuộc tính liên kết với bảng tik_attribute_values", example=1),
 *     @OA\Property(property="created_by", type="integer", description="ID người tạo bản ghi", example=1),
 *     @OA\Property(property="updated_by", type="integer", description="ID người cập nhật bản ghi", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo bản ghi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật bản ghi")
 * )
 */
class TikProductSalesAttribute extends Model
{
    protected $table = 'tik_product_sales_attributes';

    protected $fillable = [
        'product_id',
        'attribute_id',
        'value_id',
        'created_by',
        'updated_by',
    ];
    public $timestamps = true;
    public function product()
    {
        return $this->belongsTo(TikProduct::class, 'product_id');
    }

    public function attribute()
    {
        return $this->belongsTo(TikAttribute::class, 'attribute_id');
    }

    public function attributeValue()
    {
        return $this->belongsTo(TikAttributeValue::class, 'value_id');
    }
}
