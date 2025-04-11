<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="ItemGroup"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="name", type="string", description="Nhóm sản phẩm", example="VIP"),
 * )
 *
 * Class ItemGroup
 *
 */
class ItemGroup extends BaseModel
{
    protected $table = "items_groups";
    protected $fillable = [
        'id', 'name'
    ];
    protected $hidden = [
        'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
    public $timestamps = true;

}
