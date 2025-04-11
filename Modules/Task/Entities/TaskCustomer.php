<?php

namespace Modules\Task\Entities;

use App\Models\BaseModel;

class TaskCustomer extends BaseModel
{
    protected $table = "task_customers";
    protected $fillable = [
    ];
    protected $hidden = [
        'updated_at',  'updated_by'
    ];
    public $timestamps = true;
}
