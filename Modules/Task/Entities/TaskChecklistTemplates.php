<?php

namespace Modules\Task\Entities;

use App\Models\BaseModel;

class TaskChecklistTemplates extends BaseModel
{
    protected $table = "tasks_checklist_templates";
    protected $fillable = [
        'description'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
    public $timestamps = true;
}
