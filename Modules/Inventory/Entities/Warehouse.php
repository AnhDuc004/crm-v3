<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="WarehouseModel",
 *     type="object",
 *     title="Warehouse",
 *     description="Thông tin kho",
 *     required={"name", "warehouse_type"},
 *     @OA\Property(property="name", type="string", description="Tên kho", example="Kho A"),
 *     @OA\Property(property="location", type="string", description="Địa điểm kho", example="Hà Nội"),
 *     @OA\Property(property="warehouse_type", type="string", description="Loại kho", example="nguyên vật liệu"),
 * )
 */
class Warehouse extends Model
{
    protected $table = 'inv_warehouses';

    protected $fillable = [
        'name',
        'location',
        'warehouse_type'
    ];
    protected $hidden = [
        'created_by',
        'updated_by'
    ];

    public $timestamps = true;

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'warehouse_id');
    }

    public function stockReports()
    {
        return $this->hasMany(StockReport::class, 'warehouse_id');
    }

    public function inventoryCheckReports()
    {
        return $this->hasMany(InventoryCheckReport::class);
    }
    public function saleOrder()
    {
        return $this->hasMany(SalesOrder::class);
    }
}
