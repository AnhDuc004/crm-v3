<?php

namespace Modules\Campaign\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="GroupDomain"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="domain_id", type="integer", description="Mã domain", example="1"),
 * @OA\Property(property="group_id", type="integer", description="Mã group", example="1"),
 *
 * )
 *
 * Class GroupDomain
 *
 */
class GroupDomain extends BaseModel
{
    protected $table = "group_domain";
    protected $fillable = [
        'group_id', 'domain_id'
    ];
    protected $hidden = [
        'created_by','updated_at',  'updated_by', 'created_at', 'pivot'
    ];
    public $timestamps = true;

}