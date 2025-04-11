<?php

namespace Modules\Shp\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ShpTaxInfo",
 *     description="ShpTaxInfo",
 *     @OA\Property(property="id", type="integer", description="ID of the tax"),
 *     @OA\Property(property="shp_id", type="integer", description="SHP ID"),
 *     @OA\Property(property="product_id", type="integer", description="Product ID"),
 *     @OA\Property(property="ncm", type="string", description="Tax number"),
 *     @OA\Property(property="tax_type", type="string", enum={"TAXABLE", "NON_TAXABLE"}, description="Tax type"),
 *     @OA\Property(property="tax_rate", type="number", format="float", description="Tax rate"),
 *     @OA\Property(property="created_by", type="integer", description="Created by user ID"),
 *     @OA\Property(property="updated_by", type="integer", description="Updated by user ID")
 * )
 */
class ShpTaxInfo extends Model
{
    protected $table = 'shp_tax_info';
    protected $fillable = [
        'shp_id',
        'product_id',
        'ncm',
        'tax_type',
        'tax_rate',
        'created_by',
        'updated_by',
    ];
    public $timestamps = true;

    public function product()
    {
        return $this->belongsTo(ShpProduct::class, 'product_id');
    }
}
