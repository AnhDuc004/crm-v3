<?php

namespace Modules\Shp\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ShpPreorder",
 *     required={"shp_id", "product_id", "is_pre_order", "days_to_ship"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID của bản ghi, khóa chính"
 *     ),
 *     @OA\Property(
 *         property="shp_id",
 *         type="integer",
 *         format="int64",
 *         description="ID vận chuyển từ hệ thống SHP"
 *     ),
 *     @OA\Property(
 *         property="product_id",
 *         type="integer",
 *         format="int64",
 *         description="ID sản phẩm, khóa ngoại từ bảng sản phẩm"
 *     ),
 *     @OA\Property(
 *         property="is_pre_order",
 *         type="boolean",
 *         description="Chỉ định sản phẩm có phải là sản phẩm đặt trước không"
 *     ),
 *     @OA\Property(
 *         property="days_to_ship",
 *         type="integer",
 *         format="int64",
 *         description="Số ngày dự kiến giao hàng"
 *     ),
 *     @OA\Property(
 *         property="created_by",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID người tạo bản ghi"
 *     ),
 *     @OA\Property(
 *         property="updated_by",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID người cập nhật bản ghi"
 *     ),
 * )
 */
class ShpPreorder extends Model
{
    protected $table = 'shp_preorder';
    protected $fillable = [
        'shp_id',
        'product_id',
        'is_pre_order',
        'days_to_ship',
        'created_by',
        'updated_by',
    ];
    public $timestamps = true;
    public function product()
    {
        return $this->belongsTo(ShpProduct::class, 'product_id');
    }
}
