<?php

namespace Modules\Shp\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ShpBrand",
 *     type="object",
 *     title="ShpBrand",
 *     description="Model đại diện cho thương hiệu",
 *     @OA\Property(property="id", type="integer", example=1, description="ID thương hiệu, khóa chính"),
 *     @OA\Property(property="shp_id", type="integer", nullable=true, example=100, description="ID thương hiệu từ hệ thống SHP"),
 *     @OA\Property(property="original_brand_name", type="string", example="Nike", description="Tên thương hiệu gốc"),
 *     @OA\Property(property="created_by", type="integer", nullable=true, example=1, description="ID người tạo bản ghi"),
 *     @OA\Property(property="updated_by", type="integer", nullable=true, example=2, description="ID người cập nhật bản ghi"),
 * )
 */
class ShpBrand extends Model
{
    use HasFactory;

    protected $table = 'shp_brand';

    protected $fillable = [
        'shp_id',
        'original_brand_name',
        'created_by',
        'updated_by'
    ];

    public $timestamps = true;
}
