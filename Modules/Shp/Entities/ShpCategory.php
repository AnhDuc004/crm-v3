<?php

namespace Modules\Shp\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ShpCategory",
 *     type="object",
 *     title="ShpCategory",
 *     description="Danh mục sản phẩm",
 *     @OA\Property(property="id", type="integer", example=1, description="ID danh mục"),
 *     @OA\Property(property="shp_id", type="integer", nullable=true, example=1001, description="ID danh mục từ hệ thống SHP"),
 *     @OA\Property(property="category_name", type="string", maxLength=255, example="Giày thể thao", description="Tên danh mục sản phẩm"),
 *     @OA\Property(property="parent_category_id", type="integer", nullable=true, example=2, description="ID danh mục cha (nếu có)"),
 *     @OA\Property(property="created_by", type="integer", nullable=true, example=26, description="ID người tạo bản ghi"),
 *     @OA\Property(property="updated_by", type="integer", nullable=true, example=30, description="ID người cập nhật bản ghi"),
 * )
 */
class ShpCategory extends Model
{
    use HasFactory;
    protected $table = 'shp_category';

    public $timestamp = true;

    protected $fillable = [
        'shp_id',
        'category_name',
        'parent_category_id',
        'created_by',
        'updated_by'
    ];

    public function products()
    {
        return $this->hasMany(ShpProduct::class, 'category_id');
    }
}
