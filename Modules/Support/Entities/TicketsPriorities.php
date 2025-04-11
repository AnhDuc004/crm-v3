<?php

namespace Modules\Support\Entities;

use App\Models\BaseModel;

class TicketsPriorities extends BaseModel
{
    protected $table = 'tickets_priorities';

    protected $primaryKey = 'priorityid';

    protected $fillable = [
        'name'
    ];
    public $timestamps = false;
}