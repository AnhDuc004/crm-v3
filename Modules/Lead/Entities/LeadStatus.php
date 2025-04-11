<?php

namespace Modules\Lead\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="LeadStatus"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="name", type="string", description="Tên nguồn", example="VIP"),
 * @OA\Property(property="status_order", type="integer", description="Thứ tự trạng thái", example="1"),
 * @OA\Property(property="color", type="string", description="Màu trạng thái", example="#ffffff"),
 * @OA\Property(property="is_default", type="integer", description="Trạng thái", example="0.false, 1.true"),
 *
 *
 * )
 *
 * Class LeadStatus
 *
 */
class LeadStatus extends BaseModel
{
    protected $table = "leads_status";
    protected $fillable = [
        'name',
        'status_order',
        'color',
        'is_default'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];

    public $timestamps = true;
}
