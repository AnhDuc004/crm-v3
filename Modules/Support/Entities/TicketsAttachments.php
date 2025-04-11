<?php

namespace Modules\Support\Entities;

use App\Models\BaseModel;

class TicketsAttachments extends BaseModel
{
    protected $table = 'ticket_attachments';

    protected $primaryKey = 'id';

    protected $fillable = [
        'ticket_id', 
        'reply_id', 
        'file_name', 
        'file_type'
    ];
    public $timestamps = true;
}