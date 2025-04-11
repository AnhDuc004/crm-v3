<?php

namespace Modules\Tik\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TikCategory",
 *     title="TikCategory",
 *     description="Category model representing product categories",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID danh mục, khóa chính, định danh duy nhất của danh mục", example=1),
 *     @OA\Property(property="local_display_name", type="string", description="Tên hiển thị địa phương của danh mục", example="Electronics"),
 *     @OA\Property(property="parent_id", type="integer", format="int64", nullable=true, description="ID danh mục cha (Nếu có), liên kết với id của bảng này", example=null),
 *     @OA\Property(property="is_leaf", type="boolean", description="Liệu danh mục này có phải là danh mục lá (Không có danh mục con)", example=false),
 *     @OA\Property(property="status", type="object", description="Trạng thái của danh mục dưới dạng JSON (Ví dụ: {'active': true})", example={"active": true}),
 *     @OA\Property(property="created_by", type="integer", description="ID người tạo bản ghi", example=1),
 *     @OA\Property(property="updated_by", type="integer", description="ID người cập nhật bản ghi", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo bản ghi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật bản ghi")
 * )
 */
class TikCategory extends Model
{
    protected $table = 'tik_categories';

    protected $fillable = [
        'local_display_name',
        'parent_id',
        'is_leaf',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_leaf' => 'boolean',
        'status' => 'array',
    ];

    public $timestamps = true;

    public function parent()
    {
        return $this->belongsTo(TikCategory::class, 'parent_id');
    }

    public function subcategories()
    {
        return $this->hasMany(TikCategory::class, 'parent_id');
    }
}
