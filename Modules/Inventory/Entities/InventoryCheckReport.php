<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="InventoryCheckReportModel",
 *     type="object",
 *     title="InventoryCheckReport",
 *     description="Entity đại diện cho báo cáo kiểm kho",
 *     required={"warehouse_id", "check_date", "actual_stock", "stock_difference"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID báo cáo kiểm kho",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="material_id",
 *         type="integer",
 *         description="ID nguyên vật liệu (nếu là báo cáo nguyên vật liệu)",
 *         nullable=true,
 *         example=3
 *     ),
 *     @OA\Property(
 *         property="product_id",
 *         type="integer",
 *         description="ID sản phẩm (nếu là báo cáo thành phẩm)",
 *         nullable=true,
 *         example=5
 *     ),
 *     @OA\Property(
 *         property="warehouse_id",
 *         type="integer",
 *         description="ID kho",
 *         example=2
 *     ),
 *     @OA\Property(
 *         property="check_date",
 *         type="string",
 *         format="date",
 *         description="Ngày kiểm kho",
 *         example="2025-01-15"
 *     ),
 *     @OA\Property(
 *         property="actual_stock",
 *         type="number",
 *         format="float",
 *         description="Số lượng tồn kho thực tế",
 *         example=100.50
 *     ),
 *     @OA\Property(
 *         property="stock_difference",
 *         type="number",
 *         format="float",
 *         description="Chênh lệch số lượng tồn kho giữa kiểm kho và báo cáo",
 *         example=-10.25
 *     ),
 * )
 */
class InventoryCheckReport extends Model
{
    protected $table = 'inv_inventory_check_report';

    protected $fillable = [
        'material_id',
        'product_id',
        'warehouse_id',
        'check_date',
        'actual_stock',
        'stock_difference',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
