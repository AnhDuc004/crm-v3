<?php

namespace Modules\Shp\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ShpSellerStock",
 *     required={"product_id", "stock"},
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
 *         nullable=true,
 *         description="ID vận chuyển từ hệ thống SHP"
 *     ),
 *     @OA\Property(
 *         property="product_id",
 *         type="integer",
 *         format="int64",
 *         description="ID sản phẩm, khóa ngoại từ bảng sản phẩm"
 *     ),
 *     @OA\Property(
 *         property="location_id",
 *         type="string",
 *         description="ID vị trí của sản phẩm trong kho"
 *     ),
 *     @OA\Property(
 *         property="stock",
 *         type="integer",
 *         format="int64",
 *         description="Số lượng sản phẩm trong kho"
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
class ShpSellerStock extends Model
{
    protected $table = 'shp_seller_stock';
    protected $fillable = [
        'shp_id',
        'product_id',
        'location_id',
        'stock',
        'created_by',
        'updated_by',
    ];
    public $timestamps = true;

    public function product()
    {
        return $this->belongsTo(ShpProduct::class, 'product_id');
    }
}
