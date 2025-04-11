<?php

namespace Modules\Campaign\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="CampaignContent"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="file_path", type="string", description="Đường dẫn ảnh", example="abc.txt"),
 * @OA\Property(property="campaign_id", type="integer", description="Mã campaign", example="1"),
 *
 * )
 *
 * Class CampaignContent
 *
 */
class CampaignContent extends BaseModel
{
    protected $table = "campaign_content";
    protected $fillable = [
        'campaign_id', 'file_path'
    ];
    protected $hidden = [
        'created_by','updated_at',  'updated_by', 'created_at'
    ];
    public $timestamps = true;

}
