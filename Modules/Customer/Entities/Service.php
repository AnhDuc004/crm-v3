<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="Service"),
 * @OA\Property(property="serviceid", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="name", type="string", description="Tên service", example="VIP"),
 * )
 *
 * Class Service
 *
 */
class Service extends BaseModel
{
    protected $table = 'services';

    protected $primaryKey = 'serviceid';

    protected $fillable = ['name'];

    public $timestamps = false;
}