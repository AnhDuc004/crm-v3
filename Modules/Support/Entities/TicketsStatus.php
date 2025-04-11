<?php

namespace Modules\Support\Entities;

use App\Models\BaseModel;

class TicketsStatus extends BaseModel
{
    protected $table = 'tickets_status';

    protected $primaryKey = 'ticketstatusid';

    protected $fillable = [
        'name', 'isdefault', 'statuscolor', 'statusorder'
    ];
    public $timestamps = false;
}