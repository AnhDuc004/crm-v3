<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;


class ItemTax extends BaseModel
{
    protected $table = "item_tax";
    protected $fillable = [
        'item_id',
        'rel_id',
        'rel_type',
        'tax_rate',
        'tax_name',
        'tax_amount',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];
    public $timestamps = true;
}
