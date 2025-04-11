<?php

namespace Modules\Campaign\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="FbGroup"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="link", type="string", description="Tên FbGroup", example="www.google.com"),
 *
 * )
 *
 * Class FbGroup
 *
 */
class FbGroup extends BaseModel
{
    protected $table = "fbgroup";
    protected $fillable = [
        'link'
    ];
    protected $hidden = [
        'created_by','updated_at',  'updated_by', 'created_at', 'pivot'
    ];
    public $timestamps = true;

}