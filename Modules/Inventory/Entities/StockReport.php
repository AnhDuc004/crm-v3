<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="StockReportModel",
 *     type="object",
 *     title="Stock Report",
 *     description="Báo cáo xuất nhập tồn kho",
 *     required={"warehouse_id", "total_in", "total_out", "stock_balance", "actual_stock", "stock_difference"},
 *     @OA\Property(property="material_id", type="integer", description="ID nguyên vật liệu", example=1),
 *     @OA\Property(property="product_id", type="integer", description="ID sản phẩm", example=2),
 *     @OA\Property(property="warehouse_id", type="integer", description="ID kho", example=1),
 *     @OA\Property(property="total_in", type="number", format="float", description="Tổng số lượng nhập", example=150.00),
 *     @OA\Property(property="total_out", type="number", format="float", description="Tổng số lượng xuất", example=50.00),
 *     @OA\Property(property="stock_balance", type="number", format="float", description="Số lượng tồn kho", example=100.00),
 *     @OA\Property(property="actual_stock", type="number", format="float", description="Số lượng tồn kho thực tế", example=95.00),
 *     @OA\Property(property="stock_difference", type="number", format="float", description="Chênh lệch tồn kho thực tế", example=-5.00),
 * )
 */
class StockReport extends Model
{
    protected $table = 'inv_stock_report';

    protected $fillable = [
        'material_id',
        'product_id',
        'warehouse_id',
        'total_in',
        'total_out',
        'stock_balance',
        'actual_stock',
        'stock_difference',
    ];

    protected $hidden = [
        'report_date',
        'created_by',
        'updated_by',
        'updated_at',
        'created_at'
    ];

    public $timestamps = true;

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
