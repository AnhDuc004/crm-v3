<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="SpamFilters"),
 * @OA\Property(property="serviceid", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="name", type="string", description="Tên service", example="VIP"),
 * )
 *
 * Class SpamFilters
 *
 */
class SpamFilter extends BaseModel
{
    protected $table = 'spam_filters';
    protected $primaryKey = 'id';
    protected $fillable = [
        'type', 'rel_type', 'value'
    ];
    public $timestamps = false;

}