<?php

namespace Modules\Campaign\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="CampaignGroup"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="campaign_id", type="integer", description="Mã campaign", example="1"),
 * @OA\Property(property="group_id", type="integer", description="Mã group", example="1"),
 *
 * )
 *
 * Class CampaignGroup
 *
 */
class CampaignGroup extends BaseModel
{
    protected $table = "campaign_group";
    protected $fillable = [
        'campaign_id', 'group_id'
    ];
    protected $hidden = [
        'created_by','updated_at',  'updated_by', 'created_at'
    ];
    public $timestamps = true;

}
