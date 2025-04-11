<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Unit",
 *     type="object",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", description="Name of the unit")
 * )
 */
class Unit extends Model
{
    protected $table = 'inv_units';

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'created_by',
        'updated_by',
        'updated_at',
        'created_at',
    ];

    public $timestamps = true;

    public function materials()
    {
        return $this->hasMany(Material::class, 'unit_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'unit_id');
    }
}
