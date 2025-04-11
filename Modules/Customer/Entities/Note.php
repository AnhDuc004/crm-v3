<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Contract\Entities\Contract;
use Modules\Lead\Entities\Lead;
use Modules\Sale\Entities\Estimate;
/**
 *
 * @OA\Schema(
 * schema="NoteModel",
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="description", type="string", description="Mô tả", example="VIP"),
 * @OA\Property(property="rel_id", type="integer", description="Mã công việc liên quan", example="1"),
 * @OA\Property(property="rel_type", type="string", description="Tên công việc liên quan", example="customer"), 
 * @OA\Property(property="date_contacted", type="date", description="Ngày liên hệ", example="2024/10/10"),
 * 
 * 
 * )
 *
 * Class Note
 *
 */
class Note extends Model
{
    protected $table = 'notes';

    protected $fillable =
        [
            'rel_id',
            'rel_type',
            'description',
            'date_contacted'
        ];

    public $timestamps = true;

    /**
     *  Get customer belongsTo to note
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'rel_id');
    }
    /**
     *  Get lead belongsTo to note
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'rel_id');
    }
    /**
     *  Get estimate belongsTo to note
     */
    public function estimate()
    {
        return $this->belongsTo(Estimate::class, 'rel_id');
    }
    /**
     *  Get contract belongsTo to note
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'rel_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'rel_id');
    }
}
