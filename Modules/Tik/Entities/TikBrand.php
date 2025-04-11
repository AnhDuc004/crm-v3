<?php

namespace Modules\Tik\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TikBrand",
 *     title="TikBrand",
 *     description="Brand model representing product brands",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID thương hiệu, khóa chính, định danh duy nhất của thương hiệu", example=1),
 *     @OA\Property(property="name", type="string", description="Tên thương hiệu", example="Nike"),
 *     @OA\Property(property="authorized_status", type="integer", description="Trạng thái ủy quyền (2 = ủy quyền, 1 = chưa ủy quyền, 0 = không ủy quyền)", example=2),
 *     @OA\Property(property="is_t1_brand", type="boolean", description="Cờ chỉ ra liệu đây có phải là thương hiệu T1 (True/False)", example=true),
 *     @OA\Property(property="created_by", type="integer", description="ID người tạo bản ghi", example=1),
 *     @OA\Property(property="updated_by", type="integer", description="ID người cập nhật bản ghi", example=1),
 * )
 */
class TikBrand extends Model
{
    protected $table = 'tik_brands';

    protected $fillable = [
        'name',
        'authorized_status',
        'is_t1_brand',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_t1_brand' => 'boolean',
    ];
    public $timestamps = true;
}
