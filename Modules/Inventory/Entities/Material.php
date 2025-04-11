<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="MaterialModel",
 *     type="object",
 *     title="Material",
 *     description="Entity đại diện cho nguyên vật liệu",
 *     required={"name", "unit_id", "supplier_id"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID nguyên vật liệu",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Tên nguyên vật liệu",
 *         example="Sắt thép xây dựng"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         nullable=true,
 *         description="Mô tả nguyên vật liệu",
 *         example="Sắt thép xây dựng dùng cho công trình dân dụng"
 *     ),
 *     @OA\Property(
 *         property="unit_id",
 *         type="integer",
 *         description="ID của đơn vị tính",
 *         example=3
 *     ),
 *     @OA\Property(
 *         property="supplier_id",
 *         type="integer",
 *         description="ID của nhà cung cấp",
 *         example=5
 *     ),
 * )
 */
class Material extends Model
{
    protected $table = 'inv_materials';
    protected $fillable = [
        'name',
        'description',
        'unit_id',
        'supplier_id',
        'created_by',
        'updated_by'
    ];

    protected $hidden = [

        'created_at',
        'updated_at'
    ];
    public $timestamps = true;

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'inv_production_norms', 'material_id', 'product_id')
            ->withPivot('norm_quantity', 'season')
            ->withTimestamps();
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'material_id');
    }

    public function stockReports()
    {
        return $this->hasMany(StockReport::class, 'material_id');
    }

    public function inventoryCheckReports()
    {
        return $this->hasMany(InventoryCheckReport::class);
    }
}
