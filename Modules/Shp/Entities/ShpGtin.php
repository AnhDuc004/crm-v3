<?php

namespace Modules\Shp\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ShpGtin",
 *     title="ShpGtin",
 *     description="ShpGtinModel",
 *     @OA\Property(property="id", type="integer", format="int32", description="ID mã GTIN, khóa chính"),
 *     @OA\Property(property="shp_id", type="integer", format="int32", nullable=true, description="ID mã GTIN từ hệ thống SHP"),
 *     @OA\Property(property="product_id", type="integer", format="int32", description="ID sản phẩm, khóa ngoại từ bảng sản phẩm"),
 *     @OA\Property(property="gtin_code", type="string", maxLength=14, description="Mã GTIN của sản phẩm"),
 *     @OA\Property(property="created_by", type="integer", format="int32", nullable=true, description="ID người tạo bản ghi"),
 *     @OA\Property(property="updated_by", type="integer", format="int32", nullable=true, description="ID người cập nhật bản ghi"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo bản ghi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật bản ghi"),
 * )
 */
class ShpGtin extends Model
{
    protected $table = 'shp_gtin';

    protected $fillable = [
        'shp_id',
        'product_id',
        'gtin_code',
        'created_by',
        'updated_by',
    ];

    public $timestamps = true;

    public function product()
    {
        return $this->belongsTo(ShpProduct::class, 'product_id', 'id');
    }
}
