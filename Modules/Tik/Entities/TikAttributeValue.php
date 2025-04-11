<?php

namespace Modules\Tik\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TikAttributeValue",
 *     title="TikAttributeValue",
 *     description="Attribute value model representing values for attributes",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID giá trị thuộc tính, khóa chính, định danh duy nhất của giá trị thuộc tính", example=1),
 *     @OA\Property(property="attribute_id", type="integer", format="int64", description="ID thuộc tính (liên kết với bảng tik_attributes)", example=1),
 *     @OA\Property(property="name", type="string", description="Tên của giá trị thuộc tính (Ví dụ: 'Đỏ', 'XL'...)", example="Red"),
 *     @OA\Property(property="created_by", type="integer", description="ID người tạo bản ghi", example=1),
 *     @OA\Property(property="updated_by", type="integer", description="ID người cập nhật bản ghi", example=1),
 * )
 */
class TikAttributeValue extends Model
{
    protected $table = 'tik_attribute_values';

    protected $fillable = [
        'attribute_id',
        'name',
        'created_by',
        'updated_by',
    ];

    public $timestamps = true;

    public function attribute()
    {
        return $this->belongsTo(TikAttribute::class, 'attribute_id');
    }
}
