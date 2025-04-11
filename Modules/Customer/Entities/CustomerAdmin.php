<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;
use Modules\Admin\Entities\Staff;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="CustomerAdmin"),
 * @OA\Property(property="staff_id", type="integer", description="Mã người chỉ đinh", example="1"),
 * @OA\Property(property="customer_id", type="integer", description="Mã khách hàng", example="1"),
 * @OA\Property(property="date_assigned", type="date", description="Ngày tạo", example="10/10/2020"),
 * )
 *
 * Class CustomerAdmin
 *
 */
class CustomerAdmin extends BaseModel
{
    protected $table = "customer_admins";
    protected $fillable = [
       'staff_id', 'customer_id', 'date_assigned'
    ];
    protected $hidden = [
        'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
    public $timestamps = true;

    /**
     *  Get customer belongsTo to customer
     */
    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    /**
     *  Get staff belongsTo to customer
     */
    public function staff() {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
