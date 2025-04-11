<?php

namespace Modules\Contract\Entities;

use App\Models\BaseModel;
use Modules\Admin\Entities\Staff;

/**
 *
 * @OA\Schema(
 * schema="ContractCommentsModel",
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="content", type="string", description="Nội dung", example="quần áo"),
 * @OA\Property(property="contract_id", type="integer", description="Mã hợp đồng", example="1"),
 * @OA\Property(property="staff_id", type="integer", description="Mã nhân viên", example="1"),
 * @OA\Property(property="created_by", type="integer", description="Người tạo", example="1"),
 * @OA\Property(property="updated_by", type="integer", description="Người cập nhật", example="2"),
 * )
 *
 * Class ContractComments
 */
class ContractComments extends BaseModel
{
    protected $table = "contract_comments";
    protected $primaryKey = 'id';

    protected $fillable = [
        'content','contract_id','staff_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;

    /**
     *  Get contract belongTo to contract_comments
    */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     *  Get staff belongTo to contract_comments
    */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
