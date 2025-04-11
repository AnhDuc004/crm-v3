<?php

namespace Modules\Campaign\Entities;

use App\Models\BaseModel;
/**
 *
 * @OA\Schema(
 * @OA\Xml(name="Campaign"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="name", type="string", description="Tên campaign", example="THC"),
 * @OA\Property(property="active", type="integer", description="Trạng thái", example="0.false, 1.true"),
 *
 * )
 *
 * Class Campaign
 *
 */
class Campaign extends BaseModel
{
    protected $table = "campaign";
    protected $fillable = [
        'name', 'active'
    ];
    protected $hidden = [
        'created_by','updated_at',  'updated_by', 'created_at'
    ];
    public $timestamps = true;

    public function content()
    {
        return $this->hasMany(CampaignContent::class, 'campaign_id');
    }

    public function image()
    {
        return $this->hasMany(CampaignImage::class, 'campaign_id');
    }
}
