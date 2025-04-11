<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 *     schema="PaymentModeModel",
 *     @OA\Xml(name="PaymentModeModel"),
 *     @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 *     @OA\Property(property="name", type="string", description="Tên phương thức thanh toán", example="Credit Card"),
 *     @OA\Property(property="description", type="string", description="Mô tả", example="Payment by credit card"),
 *     @OA\Property(property="active", type="integer", description="Trạng thái hoạt động (1: active, 0: inactive)", example="1"),
 *     @OA\Property(property="show_on_pdf", type="boolean", description="Hiển thị trên PDF", example=true),
 *     @OA\Property(property="invoices_only", type="boolean", description="Chỉ sử dụng cho hóa đơn", example=false),
 *     @OA\Property(property="expenses_only", type="boolean", description="Chỉ sử dụng cho chi phí", example=false),
 *     @OA\Property(property="selected_by_default", type="boolean", description="Được chọn mặc định", example=true)
 * )
 */
class PaymentMode extends BaseModel
{
    protected $table = 'payment_modes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'description',
        'active',
        'show_on_pdf',
        'invoices_only',
        'expenses_only',
        'selected_by_default'
    ];

    protected $hidden = [
        "created_by",
        "updated_by",
        "created_at",
        "updated_at"
    ];

    public $timestamps = false;
}
