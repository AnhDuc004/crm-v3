<?php

namespace Modules\Task\Entities;

use App\Models\BaseModel;
use App\Utils\Models\User;

class TaskNote extends BaseModel
{
    protected $table = "task_note";
    protected $fillable = [
        'task_id', 'user_id', 'description'
    ];
    protected $hidden = [
        'created_by', 'created_at', 'updated_at',  'updated_by'
    ];
    public $timestamps = true;

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
