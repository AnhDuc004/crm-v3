<?php

namespace Modules\Tik\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TikProductImage",
 *     title="TikProductImage",
 *     description="Product image model representing product images",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID hình ảnh, khóa chính, định danh duy nhất của hình ảnh", example=1),
 *     @OA\Property(property="url_list", type="array", description="Các URL hình ảnh dưới dạng JSON (Danh sách các URL)",
 *         @OA\Items(type="string", example="https://example.com/images/product1.jpg")
 *     ),
 *     @OA\Property(property="thumb_url_list", type="array", description="Các URL của ảnh thumbnail dưới dạng JSON",
 *         @OA\Items(type="string", example="https://example.com/images/product1_thumb.jpg")
 *     ),
 *     @OA\Property(property="height", type="integer", description="Chiều cao của hình ảnh", example=1200),
 *     @OA\Property(property="width", type="integer", description="Chiều rộng của hình ảnh", example=800),
 *     @OA\Property(property="product_id", type="integer", format="int64", description="ID sản phẩm liên kết với bảng tik_products", example=1),
 *     @OA\Property(property="created_by", type="integer", description="ID người tạo bản ghi", example=1),
 *     @OA\Property(property="updated_by", type="integer", description="ID người cập nhật bản ghi", example=1),
 * )
 */
class TikProductImage extends Model
{
    protected $table = 'tik_product_images';

    protected $fillable = [
        'url_list',
        'thumb_url_list',
        'height',
        'width',
        'product_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'url_list' => 'array',
        'thumb_url_list' => 'array',
    ];
    public $timestamps = true;
    public function product()
    {
        return $this->belongsTo(TikProduct::class, 'product_id');
    }
}
