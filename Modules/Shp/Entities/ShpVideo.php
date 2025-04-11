<?php

namespace Modules\Shp\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ShpVideo",
 *     type="object",
 *     required={"product_id", "video_url"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="The unique identifier of the video"
 *     ),
 *     @OA\Property(
 *         property="shp_id",
 *         type="integer",
 *         description="ID của video trong hệ thống SHP"
 *     ),
 *     @OA\Property(
 *         property="product_id",
 *         type="integer",
 *         description="ID sản phẩm"
 *     ),
 *     @OA\Property(
 *         property="video_url",
 *         type="string",
 *         description="URL của video"
 *     ),
 *     @OA\Property(
 *         property="thumbnail_url",
 *         type="string",
 *         description="URL của thumbnail video"
 *     ),
 *     @OA\Property(
 *         property="duration",
 *         type="integer",
 *         description="Số giây của video"
 *     ),
 *     @OA\Property(
 *         property="created_by",
 *         type="integer",
 *         description="Nguời tạo video"
 *     ),
 *     @OA\Property(
 *         property="updated_by",
 *         type="integer",
 *         description="Người cập nhật video"
 *     ),
 * )
 */

class ShpVideo extends Model
{
    protected $table = 'shp_videos';
    protected $fillable = [
        'shp_id',
        'product_id',
        'video_url',
        'thumbnail_url',
        'duration',
        'created_by',
        'updated_by',
    ];
    public $timestamps = true;

    public function product()
    {
        return $this->belongsTo(ShpProduct::class, 'product_id');
    }
}
