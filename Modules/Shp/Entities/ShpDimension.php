<?php

namespace Modules\Shp\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Shp\Entities\ShpProduct;

/**
 * @OA\Schema(
 *     schema="ShpDimension",
 *     title="ShpDimension",
 *     description="Model lưu trữ thông tin kích thước gói hàng",
 *     type="object",
 *     required={"shp_id", "product_id", "package_width", "package_length", "package_height"},
 *     
 *     @OA\Property(property="id", type="integer", format="int64", example=1, description="ID của kích thước gói hàng"),
 *     @OA\Property(property="shp_id", type="integer", example=10, description="ID của đơn hàng vận chuyển"),
 *     @OA\Property(property="product_id", type="integer", example=100, description="ID sản phẩm liên kết"),
 *     @OA\Property(property="package_width", type="number", format="float", example=12.5, description="Chiều rộng của gói hàng"),
 *     @OA\Property(property="package_length", type="number", format="float", example=20.3, description="Chiều dài của gói hàng"),
 *     @OA\Property(property="package_height", type="number", format="float", example=5.7, description="Chiều cao của gói hàng"),
 *    )
 */
class ShpDimension extends Model
{
    protected $table = 'shp_dimensions';
    protected $fillable = [
        'shp_id',
        'product_id',
        'package_width',
        'package_length',
        'package_height',
        'created_by',
        'updated_by'
    ];
    public $timestamps = true;

    public function product()
    {
        return $this->belongsTo(ShpProduct::class, 'product_id');
    }
}
