<?php

namespace Modules\Tik\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TikAttribute",
 *     title="TikAttribute",
 *     description="Attribute model representing product attributes",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID thuộc tính, khóa chính, định danh duy nhất của thuộc tính", example=1),
 *     @OA\Property(property="name", type="string", description="Tên thuộc tính (Ví dụ: Màu sắc, Kích thước...)", example="Color"),
 *     @OA\Property(property="attribute_type", type="integer", description="Loại thuộc tính (Ví dụ: 1 cho 'Text', 2 cho 'Number', 3 cho 'Date'...)", example=1),
 *     @OA\Property(property="value_data_format", type="string", description="Định dạng dữ liệu cho giá trị thuộc tính (Ví dụ: 'String', 'Integer', 'Date'...)", example="String"),
 *     @OA\Property(property="created_by", type="integer", description="ID người tạo bản ghi", example=1),
 *     @OA\Property(property="updated_by", type="integer", description="ID người cập nhật bản ghi", example=1),
 * )
 */
class TikAttribute extends Model
{
    protected $table = 'tik_attributes';

    protected $fillable = [
        'name',
        'attribute_type',
        'value_data_format',
        'created_by',
        'updated_by',
    ];

    public $timestamps = true;
    public function attributeValues()
    {
        return $this->hasMany(TikAttributeValue::class, 'attribute_id');
    }
}
