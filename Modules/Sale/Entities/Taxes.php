<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;
/**
 *
 * @OA\Schema(
 * @OA\Xml(name="Taxes"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="name", type="string", description="Tên thuế", example="1"),
 * @OA\Property(property="taxrate", type="number", format="double", example="10.00"),
 * 
 * 
 * )
 *
 * Class Taxes
 *
 */
class Taxes extends BaseModel
{
    protected $table = 'taxes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name', 'taxrate'
    ];
    public $timestamps = false;
}