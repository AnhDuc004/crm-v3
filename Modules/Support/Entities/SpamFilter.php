<?php

namespace Modules\Support\Entities;

use App\Models\BaseModel;

class SpamFilter extends BaseModel
{
    protected $table = 'spam_filters';

    protected $primaryKey = 'id';

    protected $fillable = [
        'type', 'rel_type', 'value'
    ];

    public $timestamps = false;

}