<?php

namespace Modules\Task\Entities;

use App\Models\BaseModel;

class TaskType extends BaseModel
{
    protected $table = "task_types";
    protected $fillable = [
        'name', 'description', 'status'
    ];
    protected $hidden = [
        'created_by','updated_at',  'updated_by', 'created_at'
    ];
    public $timestamps = true;

    public function tasks()
    {
        return $this->hasMany(Task::class,'task_type_id');
    }
}
