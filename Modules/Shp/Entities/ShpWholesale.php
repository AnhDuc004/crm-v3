<?php

namespace Modules\Shp\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ShpWholesale",
 *     type="object",
 *     required={"product_id", "min_count", "max_count", "unit_price"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="The unique identifier of the wholesale"
 *     ),
 *     @OA\Property(
 *         property="shp_id",
 *         type="integer",
 *         description="ID của wholesale trong hệ thống SHP"
 *     ),
 *     @OA\Property(
 *         property="product_id",
 *         type="integer",
 *         description="ID của sản phẩm"
 *     ),
 *     @OA\Property(
 *         property="min_count",
 *         type="integer",
 *         description="Số lượng tối thiểu trong wholesale"
 *     ),
 *     @OA\Property(
 *         property="max_count",
 *         type="integer",
 *         description="Số lượng tối đa trong wholesale"
 *     ),
 *     @OA\Property(
 *         property="unit_price",
 *         type="number",
 *         format="float",
 *         description="Giá mỗi đơn vị trong wholesale"
 *     ),
 *     @OA\Property(
 *         property="created_by",
 *         type="integer",
 *         description="Người tạo wholesale"
 *     ),
 *     @OA\Property(
 *         property="updated_by",
 *         type="integer",
 *         description="Người cập nhật wholesale"
 *     ),
 * )
 */

class ShpWholesale extends Model
{
    protected $table = 'shp_wholesale';
    protected $fillable = [
        'shp_id',
        'product_id',
        'min_count',
        'max_count',
        'unit_price',
        'created_by',
        'updated_by',
    ];
    public $timestamps = true;

    public function product()
    {
        return $this->belongsTo(ShpProduct::class, 'product_id');
    }
}
