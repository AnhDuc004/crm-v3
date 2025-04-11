<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Supplier",
 *     type="object",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the supplier"
 *     ),
 *     @OA\Property(
 *         property="address",
 *         type="string",
 *         description="Address of the supplier"
 *     ),
 *     @OA\Property(
 *         property="phone",
 *         type="string",
 *         description="Phone number of the supplier"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         description="Email of the supplier"
 *     ),
 * )
 */
class Supplier extends Model
{
    protected $table = 'inv_suppliers';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    public $timestamps = true;

    public function materials()
    {
        return $this->hasMany(Material::class, 'supplier_id');
    }
}
