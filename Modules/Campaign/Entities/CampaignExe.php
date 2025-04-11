<?php

namespace Modules\Campaign\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="CampaignExe"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="campaign_id", type="integer", description="Mã campaign", example="1"),
 * @OA\Property(property="group_id", type="integer", description="Mã group", example="1"),
 * @OA\Property(property="content_id", type="integer", description="Mã content", example="1"),
 * @OA\Property(property="image_id", type="integer", description="Mã ảnh", example="1"),
 *
 * )
 *
 * Class CampaignExe
 *
 */
class CampaignExe extends BaseModel
{
    protected $table = "campaign_exe";
    protected $fillable = [
        'campaign_id', 'group_id', 'content_id', 'image_id'
    ];
    protected $hidden = [
        'created_by','updated_at',  'updated_by', 'created_at'
    ];
    public $timestamps = true;

}
