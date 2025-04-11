<?php

namespace Modules\Contract\Entities;

use App\Models\BaseModel;
use Modules\Admin\Entities\Staff;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="ContractRenewals"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="contract_id", type="integer", description="Mã hợp đồng", example="1"),
 * @OA\Property(property="old_start_date", type="date", description="Ngày bắt đầu cũ", example="10/10/2020"),
 * @OA\Property(property="new_start_date", type="date", description="Ngày bắt đầu mới", example="10/10/2020"),
 * @OA\Property(property="old_end_date", type="date", description="Ngày kết thúc cũ", example="110/10/2020"),
 * @OA\Property(property="old_value", type="string", description="Giá trị cũ", example="0.123"),
 * @OA\Property(property="new_value", type="string", description="Giá trị mới", example="0.124"),
 * @OA\Property(property="date_renewed", type="date", description="Ngày gia hạn", example="10/10/2020"),
 * @OA\Property(property="renewed_by_staff_id", type="integer", description="Người gia hạn", example="1"),
 * @OA\Property(property="is_on_old_expiry_notified", type="integer", description="Đã hết hạn cũ được thông báo", example="0.false, 1.true"),
 * )
 *
 * Class ContractRenewals
 */
class ContractRenewals extends BaseModel
{
    protected $table = "contract_renewals";
    protected $primaryKey = 'id';
    protected $fillable = [
        'contract_id','old_start_date','new_start_date', 'old_end_date', 'new_end_date', 'old_value',
        'new_value', 'date_renewed', 'renewed_by', 'renewed_by_staff_id', 'is_on_old_expiry_notified'
    ];
    public $timestamps = true;
    /**
     *  Get contract belongTo to contract_renewals
    */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }
}
