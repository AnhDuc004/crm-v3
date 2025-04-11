<?php

namespace Modules\Campaign\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="CampaignImage"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="image_path", type="string", description="Đường dẫn ảnh", example="abc.txt"),
 * @OA\Property(property="campaign_id", type="integer", description="Mã campaign", example="1"),
 *
 * )
 *
 * Class CampaignImage
 *
 */
class CampaignImage extends BaseModel
{
    protected $table = "campaign_image";
    protected $fillable = [
        'campaign_id', 'image_path'
    ];
    protected $hidden = [
        'created_by','updated_at',  'updated_by', 'created_at'
    ];
    public $timestamps = true;

}
