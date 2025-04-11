<?php

namespace Modules\Shp\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ShpAttribute",
 *     required={"product_id", "original_value_name"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="shp_id", type="integer", nullable=true, example=1001),
 *     @OA\Property(property="product_id", type="integer", example=101),
 *     @OA\Property(property="attribute_value_list", type="object", example={"color": "red", "size": "M"}),
 *     @OA\Property(property="original_value_name", type="string", example="Red"),
 *     @OA\Property(property="value_unit", type="string", example="cm"),
 * )
 */

class ShpAttribute extends Model
{

    use HasFactory;

    protected $table = 'shp_attributes';

    protected $fillable = [
        'shp_id',
        'product_id',
        'attribute_value_list',
        'original_value_name',
        'value_unit',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'attribute_value_list' => 'json',
    ];

    public $timestamp = true;

    public function product()
    {
        return $this->belongsTo(ShpProduct::class, 'product_id');
    }
}
