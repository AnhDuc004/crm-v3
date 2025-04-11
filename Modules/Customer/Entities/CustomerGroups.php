<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="CustomerGroups"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="groupId", type="integer", description="Mã nhóm khách hàng", example="1"),
 * @OA\Property(property="clientId", type="integer", description="Mã khách hàng", example="1"),
 * )
 *
 * Class CustomerGroups
 *
 */
class CustomerGroups extends BaseModel
{
    protected $table = "customer_groups";
    protected $fillable = [
       'id', 'groupId', 'customerId'
    ];
    protected $hidden = [
        'created_at', 'updated_at', 'created_by', 'updated_by', 'pivot'
    ];
    public $timestamps = false;

    /**
     *  Get customer belongsTo to customer
     */
    public function customer() {
        return $this->belongsTo(Customer::class, 'customerId');
    }

    /**
     *  Get group belongsTo to customer
     */
    public function group() {
        return $this->belongsTo(Group::class, 'groupId');
    }
}
