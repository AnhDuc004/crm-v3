<?php

namespace Modules\Tik\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TikSku",
 *     title="TikSku",
 *     description="SKU model representing stock keeping units",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID SKU, khóa chính, định danh duy nhất của SKU", example=1),
 *     @OA\Property(property="sku_id", type="integer", format="int64", description="ID SKU (dành cho hệ thống quản lý SKU)", example=123456),
 *     @OA\Property(property="product_id", type="integer", format="int64", description="ID sản phẩm liên kết với bảng tik_products", example=1),
 *     @OA\Property(property="seller_sku", type="string", description="SKU của người bán", example="SELLER-123"),
 *     @OA\Property(property="price", type="object", description="Giá sản phẩm dưới dạng JSON (Danh sách các mức giá, giá gốc, giá sau thuế...)", 
 *         example={"original": 100, "discounted": 80, "currency": "USD"}
 *     ),
 *     @OA\Property(property="stock_infos", type="object", description="Thông tin kho dưới dạng JSON (Số lượng tồn kho, trạng thái kho...)",
 *         example={"quantity": 50, "warehouse_id": 5, "status": "in_stock"}
 *     ),
 *     @OA\Property(property="created_by", type="integer", description="ID người tạo bản ghi", example=1),
 *     @OA\Property(property="updated_by", type="integer", description="ID người cập nhật bản ghi", example=1),
 * )
 */
class TikSku extends Model
{
    protected $table = 'tik_skus';

    protected $fillable = [
        'sku_id',
        'product_id',
        'seller_sku',
        'price',
        'stock_infos',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price' => 'array',
        'stock_infos' => 'array',
    ];
    public $timestamps = true;
    public function product()
    {
        return $this->belongsTo(TikProduct::class, 'product_id');
    }
}
