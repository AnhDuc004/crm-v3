<?php

namespace Modules\Tik\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TikProductCertification",
 *     title="TikProductCertification",
 *     description="Product certification model representing product certifications",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID chứng nhận sản phẩm", example=1),
 *     @OA\Property(property="name", type="string", description="Tên chứng nhận (Ví dụ: ISO 9001, CE)", example="ISO 9001"),
 *     @OA\Property(property="files", type="array", description="Các tệp chứng nhận dưới dạng JSON (Danh sách các tệp đính kèm)",
 *         @OA\Items(type="string", example="file_id_123")
 *     ),
 *     @OA\Property(property="images", type="array", description="Các hình ảnh chứng nhận dưới dạng JSON",
 *         @OA\Items(type="string", example="image_url_123")
 *     ),
 *     @OA\Property(property="title", type="string", description="Tiêu đề chứng nhận (Ví dụ: 'Chứng nhận chất lượng')", example="Quality Certificate"),
 *     @OA\Property(property="product_id", type="integer", format="int64", description="ID sản phẩm liên kết với bảng tik_products", example=1),
 *     @OA\Property(property="created_by", type="integer", description="ID người tạo bản ghi", example=1),
 *     @OA\Property(property="updated_by", type="integer", description="ID người cập nhật bản ghi", example=1),
 * )
 */
class TikProductCertification extends Model
{
    protected $table = 'tik_product_certifications';

    protected $fillable = [
        'name',
        'files',
        'images',
        'title',
        'product_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'files' => 'array',
        'images' => 'array',
    ];
    public $timestamps = true;
    public function product()
    {
        return $this->belongsTo(TikProduct::class, 'product_id');
    }
}
