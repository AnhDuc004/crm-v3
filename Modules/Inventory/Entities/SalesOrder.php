<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Customer\Entities\Customer;

/**
 * @OA\Schema(
 *     schema="SalesOrderModel",
 *     type="object",
 *     title="SalesOrder",
 *     description="Entity đại diện cho đơn bán hàng",
 *     required={"customer_id", "order_number", "order_date", "total_amount", "status"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID đơn bán hàng",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="customer_id",
 *         type="integer",
 *         description="ID khách hàng",
 *         example=5
 *     ),
 *     @OA\Property(
 *         property="order_number",
 *         type="string",
 *         description="Mã đơn bán hàng",
 *         example="SO12345"
 *     ),
 *     @OA\Property(
 *         property="warehouse_id",
 *         type="string",
 *         description="Mã kho",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="order_date",
 *         type="string",
 *         format="date",
 *         description="Ngày bán hàng",
 *         example="2025-01-15"
 *     ),
 *     @OA\Property(
 *         property="total_amount",
 *         type="number",
 *         format="float",
 *         description="Tổng giá trị đơn hàng",
 *         example=1000.50
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Trạng thái đơn hàng",
 *         example="Đang xử lý"
 *     ),
 * )
 */
class SalesOrder extends Model
{
    protected $table = 'inv_sales_orders';

    protected $fillable = [
        'customer_id',
        'order_number',
        'order_date',
        'warehouse_id',
        'total_amount',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
    public $timestamps = true;

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class, 'sales_order_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
