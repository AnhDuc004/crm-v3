<?php

namespace Modules\Shp\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ShpImage",
 *     required={"product_id", "image_url"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID hình ảnh, khóa chính"
 *     ),
 *     @OA\Property(
 *         property="shp_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID hình ảnh từ hệ thống SHP"
 *     ),
 *     @OA\Property(
 *         property="product_id",
 *         type="integer",
 *         format="int64",
 *         description="ID sản phẩm, khóa ngoại từ bảng sản phẩm"
 *     ),
 *     @OA\Property(
 *         property="image_url",
 *         type="string",
 *         maxLength=255,
 *         description="URL của hình ảnh sản phẩm"
 *     ),
 *     @OA\Property(
 *         property="image_ratio",
 *         type="string",
 *         enum={"1:1", "3:4"},
 *         default="1:1",
 *         nullable=true,
 *         description="Tỷ lệ hình ảnh (1:1 hoặc 3:4)"
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
 *     )
 * )
 */
class ShpImgae extends Model
{
    protected $table = 'shp_images';
    protected $fillable = [
        'shp_id',
        'product_id',
        'image_url',
        'image_ratio',
        'created_by',
        'updated_by'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;
    public function product()
    {
        return $this->belongsTo(ShpProduct::class);
    }
}
