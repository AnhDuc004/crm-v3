<?php

namespace Modules\Lead\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="LeadActivityLog"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="lead_id", type="integer", description="Mã khách hàng tiềm năng", example="1"),
 * @OA\Property(property="description", type="string", description="Mô tả", example="Thanh Hà"),
 * @OA\Property(property="date", type="date", description="Ngày", example="10/10/2020"),
 * @OA\Property(property="staff_id", type="integer", description="Mã người tạo", example="1"),
 * @OA\Property(property="full_name", type="string", description="Tên người tạo", example="Hiep Hoang"),
 * @OA\Property(property="custom_activity", type="integer", description="Nhật ký hoạt động", example="0.false, 1.true"),
 *
 *
 * )
 *
 * Class LeadActivityLog
 *
 */

class LeadActivityLog extends BaseModel
{
    protected $table = "lead_activity_log";
    protected $fillable = [
       'lead_id', 'description', 'date', 'staff_id', 'full_name', 'custom_activity'
    ];
    protected $hidden = [
        'created_at', 'updated_at', 'created_by', 'updated_by', 'additional_data',
    ];
    public $timestamps = true;
}
