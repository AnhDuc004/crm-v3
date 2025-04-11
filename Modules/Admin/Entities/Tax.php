<?php

namespace Modules\Admin\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * schema="TaxModel",
 * @OA\Xml(name="TaxModel"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="name", type="integer", description="Tên", example="VIP"),
 * @OA\Property(property="taxrate", type="number", format="double", description="Tỉ lệ", example="10.4"),
 * )
 *
 * Class Tax
 *
 */
class Tax extends BaseModel
{
    protected $table = 'taxes';

    protected $fillable = [
        'name',
        'taxrate'
    ];
}