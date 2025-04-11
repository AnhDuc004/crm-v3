<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="InventoryTransactionModel",
 *     type="object",
 *     title="Inventory Transaction",
 *     description="Thông tin giao dịch kho",
 *     required={"transaction_type", "quantity", "warehouse_id"},
 *     @OA\Property(property="transaction_type", type="string", description="Loại giao dịch", example="0.Nhập 1.Xuất"),
 *     @OA\Property(property="material_id", type="integer", description="Nguyên vật liệu liên quan", example=1),
 *     @OA\Property(property="product_id", type="integer", description="Sản phẩm liên quan", example=2),
 *     @OA\Property(property="quantity", type="number", format="float", description="Số lượng giao dịch", example=100.50),
 *     @OA\Property(property="warehouse_id", type="integer", description="ID kho", example=1),
 *     @OA\Property(property="transaction_date", type="string", format="date-time", description="Ngày giao dịch", example="2025-01-14T10:00:00Z"),
 * )
 */
class InventoryTransaction extends Model
{
    protected $table = 'inv_inventory_transactions';

    protected $fillable = [
        'transaction_type',
        'material_id',
        'product_id',
        'quantity',
        'warehouse_id',
        'transaction_date',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public $timestamps = true;

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
