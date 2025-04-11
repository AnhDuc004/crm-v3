<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Inventory\Entities\Unit;

/**
 * @OA\Schema(
 *     schema="ProductModel",
 *     type="object",
 *     title="Product",
 *     description="Entity đại diện cho sản phẩm",
 *     required={"name", "unit_id"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID sản phẩm",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Tên sản phẩm",
 *         example="Sản phẩm A"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         nullable=true,
 *         description="Mô tả sản phẩm",
 *         example="Mô tả về sản phẩm A"
 *     ),
 *     @OA\Property(
 *         property="unit_id",
 *         type="integer",
 *         description="ID của đơn vị tính",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="unit",
 *         type="object",
 *         description="Đơn vị tính của sản phẩm",
 *         ref="#/components/schemas/Unit"
 *     )
 * )
 */
class Product extends Model
{
    protected $table = 'inv_products';

    protected $fillable = ['name', 'description', 'unit_id', 'created_by', 'updated_by'];

    protected $hidden = ['created_at', 'updated_at'];

    public $timestamps = true;

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function materials()
    {
        return $this->belongsToMany(Material::class, 'inv_production_norms', 'product_id', 'material_id')
            ->withPivot('norm_quantity', 'season')
            ->withTimestamps();
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'product_id');
    }

    public function stockReports()
    {
        return $this->hasMany(StockReport::class, 'product_id');
    }

    public function inventoryCheckReports()
    {
        return $this->hasMany(InventoryCheckReport::class);
    }

    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class, 'product_id');
    }
}
