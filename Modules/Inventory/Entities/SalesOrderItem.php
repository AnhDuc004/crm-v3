<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="SalesOrderItemModel",
 *     type="object",
 *     required={"sales_order_id", "product_id", "quantity", "price"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID of the Sales Order Item",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="sales_order_id",
 *         type="integer",
 *         description="ID of the sales order",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="product_id",
 *         type="integer",
 *         description="ID of the product",
 *         example=10
 *     ),
 *     @OA\Property(
 *         property="quantity",
 *         type="number",
 *         format="decimal",
 *         description="Quantity of the product in the order",
 *         example=2.00
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="decimal",
 *         description="Price of the product in the order",
 *         example=500.00
 *     ),
 * )
 */
class SalesOrderItem extends Model
{
    protected $table = 'inv_sales_order_items';

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'quantity',
        'price',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    public $timestamps = true;

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
