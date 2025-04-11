<?php

namespace Modules\Lead\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * schema="LeadSourceModel",
 * @OA\Xml(name="LeadSource"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="name", type="string", description="Tên nguồn", example="VIP"),
 * )
 *
 * Class LeadSource
 *
 */
class LeadSource extends BaseModel
{
    protected $table = "leads_sources";
    protected $fillable = [
        'name'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'datecreated',
        'total_tax',
        'hash',
        'date_converted',
        'pipeline_order',
        'is_expiry_notified',
        'acceptance_firstname',
        'acceptance_lastname',
        'acceptance_email',
        'acceptance_date',
        'acceptance_id',
        'signature'

    ];
    public $timestamps = true;
}
