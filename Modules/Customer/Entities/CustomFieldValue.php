<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;
use Modules\Lead\Entities\Lead;

/**
 *
 * @OA\Schema(
 * schema="CustomFieldValueModel",
 * @OA\Xml(name="customFieldValueModel"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="rel_id", type="integer", description="ID rel", example="1"),
 * @OA\Property(property="field_id", type="integer", description="ID field", example="1"),
 * @OA\Property(property="field_to", type="string", description="field to", example="abc"),
 * @OA\Property(property="value", type="string", description="nội dung", example="abc"),
 *
 * )
 *
 * Class CustomFieldValue
 *
 */

class CustomFieldValue extends BaseModel
{
    protected $table = 'customfieldsvalues';
    protected $primaryKey = 'id';
    protected $fillable = [
        'rel_id', 'field_id', 'field_to', 'value'
    ];
    protected $hidden = [
        'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
    public $timestamps = true;

    /**
     *  Get customField belongsTo to CustomFieldValue
     */
    public function customField() {
       return $this->belongsTo(CustomField::class, 'rel_id');
    }
    /**
     *  Get lead belongsTo to CustomFieldValue
     */
    public function lead() {
       return $this->belongsTo(Lead::class, 'field_id');
    }

}