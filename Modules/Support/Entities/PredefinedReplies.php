<?php

namespace Modules\Support\Entities;

use App\Models\BaseModel;

class PredefinedReplies extends BaseModel
{
    protected $table = 'tickets_predefined_replies';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'message'
    ];

    public $timestamps = false;

}