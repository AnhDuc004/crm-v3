<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="Group"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="name", type="string", description="Nhóm khách hàng", example="VIP"),
 * )
 *
 * Class Group
 *
 */
class Group extends BaseModel
{
    protected $table = "groups";
    protected $primaryKey = 'id';
    protected $fillable = [
       'id', 'name'
    ];
    protected $hidden = [
        'created_at', 'updated_at', 'created_by', 'updated_by', 'pivot'
    ];
    public $timestamps = true;

    /**
     *  Get customer belongsToMany to group
     */
    public function customers()
    {
        return $this->belongsToMany(Customer::class, CustomerGroups::class, 'customerId', 'groupId');
    }
}
