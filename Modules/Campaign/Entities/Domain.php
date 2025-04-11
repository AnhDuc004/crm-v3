<?php

namespace Modules\Campaign\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="Domain"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="domain", type="string", description="Tên domain", example="Hà Nội"),
 *
 * )
 *
 * Class Domain
 *
 */
class Domain extends BaseModel
{
    protected $table = "domain";
    protected $fillable = [
        'domain'
    ];
    protected $hidden = [
        'created_by','updated_at',  'updated_by', 'created_at'
    ];
    public $timestamps = true;

    public function groups()
    {
        return $this->belongsToMany(FbGroup::class, GroupDomain::class, 'domain_id', 'group_id');
    }

}
