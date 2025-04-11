<?php

namespace Modules\KnowledgeBase\Entities;

use App\Models\BaseModel;

class KnowledgeBaseGroup extends BaseModel
{
    protected $table = 'knowledge_base_groups';

    protected $primaryKey = 'groupid';

    protected $fillable = [
        'name', 'group_slug', 'description', 'active', 'color', 'group_order'
    ];
    public $timestamps = false;
}