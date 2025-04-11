<?php

namespace Modules\Support\Entities;

use App\Models\BaseModel;

class TicketsPipeLog extends BaseModel
{
    protected $table = 'tickets_pipe_log';

    protected $fillable = [
        'date', 'email_to', 'name', 'subject', 'message', 'email', 'status'
    ];
    
}