<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;
/**
 *
 * @OA\Schema(
 * schema="PaymentModel",
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="invoice_id", type="integer", description="Mã hóa đơn", example="1"),
 * @OA\Property(property="amount", type="number", format="double", description="Tổng tiền", example="5000.00"),
 * @OA\Property(property="payment_mode", type="integer", description="Mã ngân hàng thanh toán", example="1"), 
 * @OA\Property(property="date", type="date", description="Từ ngày", example="2020/10/10"),
 * @OA\Property(property="date_recorded", type="date", description="Từ ngày", example="2020/10/10"),
 * @OA\Property(property="transaction_id", type="integer", description="Mã giao dịch", example="1"),
 * @OA\Property(property="note", type="string", description="Ghi chú", example="abc"),
 * 
 * )
 *
 * Class Payment
 *
 */
class Payment extends BaseModel
{
    protected $table = 'payment';
    protected $primaryKey = 'id';
    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_mode',
        'date_recorded',
        'date',
        'note',
        'transaction_id',
        'payment_method',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_by',
        'updated_by'
    ];
    public $timestamps = true;
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
    public function mode()
    {
        return $this->belongsTo(PaymentMode::class, 'payment_mode');
    }
}