<?php

namespace Modules\Tik\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TikProduct",
 *     title="TikProduct",
 *     description="Product model representing products",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID sản phẩm, khóa chính, định danh duy nhất của sản phẩm", example=1),
 *     @OA\Property(property="name", type="string", description="Tên sản phẩm", example="iPhone 13 Pro"),
 *     @OA\Property(property="product_id", type="integer", format="int64", description="ID sản phẩm trong hệ thống", example=12345),
 *     @OA\Property(property="status", type="integer", description="Trạng thái sản phẩm (Ví dụ: 4 - Đang bán, 5 - Ngừng bán...)", example=4),
 *     @OA\Property(property="create_time", type="integer", description="Thời gian tạo sản phẩm (dạng Unix timestamp)", example=1640995200),
 *     @OA\Property(property="update_time", type="integer", description="Thời gian cập nhật sản phẩm (dạng Unix timestamp)", example=1641081600),
 *     @OA\Property(property="total", type="integer", description="Tổng số sản phẩm có sẵn", example=100),
 * )
 */
class TikProduct extends Model
{
    protected $table = 'tik_products';

    protected $fillable = [
        'name',
        'product_id',
        'status',
        'create_time',
        'update_time',
        'total',
        'created_by',
        'updated_by',
    ];

    public $timestamps = true;
    public function certifications()
    {
        return $this->hasMany(TikProductCertification::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(TikProductImage::class, 'product_id');
    }

    public function salesAttributes()
    {
        return $this->hasMany(TikProductSalesAttribute::class, 'product_id');
    }

    public function skus()
    {
        return $this->hasMany(TikSku::class, 'product_id');
    }
}
