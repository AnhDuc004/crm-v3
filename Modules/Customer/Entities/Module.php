<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="Module"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="module_name", type="string", description="Tên mô đun", example="VIP"),
 * @OA\Property(property="installed_version", type="string", description="Phiên bản cài đặt", example="VIP"),
 * @OA\Property(property="active", type="integer", description="Hoạt động", example="0.false, 1.true"),
 * )
 *
 * Class Module
 *
 */
class Module extends BaseModel
{
    protected $table = 'modules';

    protected $fillable = [
        'module_name', 'installed_version', 'active'
    ];

    public $timestamps = false;
}