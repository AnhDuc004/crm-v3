<?php

namespace Modules\Shp\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ShpLogistic",
 *     required={"product_id", "enabled"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID vận chuyển, khóa chính"
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
 *         property="shipping_fee",
 *         type="number",
 *         format="float",
 *         nullable=true,
 *         description="Phí vận chuyển của sản phẩm"
 *     ),
 *     @OA\Property(
 *         property="enabled",
 *         type="boolean",
 *         description="Trạng thái kích hoạt vận chuyển"
 *     ),
 *     @OA\Property(
 *         property="is_free",
 *         type="boolean",
 *         default=false,
 *         description="Chỉ định miễn phí vận chuyển cho người mua hay không"
 *     ),
 *     @OA\Property(
 *         property="size_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID kích thước sản phẩm nếu có"
 *     ),
 *     @OA\Property(
 *         property="shipping_fee_type",
 *         type="string",
 *         enum={"CUSTOM_PRICE", "SIZE_SELECTION"},
 *         nullable=true,
 *         description="Loại phí vận chuyển"
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
class ShpLogistic extends Model
{
    protected $table = 'shp_logistics';
    protected $fillable = [
        'shp_id',
        'product_id',
        'shipping_fee',
        'enabled',
        'is_free',
        'size_id',
        'shipping_fee_type',
        'created_by',
        'updated_by'
    ];
    protected $casts = [
        'enabled' => 'boolean',
        'is_free' => 'boolean'
    ];

    public $timestamps = true;
    public function product()
    {
        return $this->belongsTo(ShpProduct::class, 'product_id', 'id');
    }
}
