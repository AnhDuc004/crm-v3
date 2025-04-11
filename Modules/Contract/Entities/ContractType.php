<?php

namespace Modules\Contract\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="ContractType"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="name", type="string", description="Nhóm hợp đồng", example="VIP"),
 *
 * )
 *
 * Class ContractType
 *
 */
class ContractType extends BaseModel
{
    protected $table = "contracts_types";
    protected $primaryKey = 'id';
    protected $fillable = [
        'name'
    ];
    protected $hidden = [

    ];
    public $timestamps = false;

}
