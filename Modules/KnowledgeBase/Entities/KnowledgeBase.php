<?php

namespace Modules\KnowledgeBase\Entities;

use App\Models\BaseModel;

class KnowledgeBase extends BaseModel
{
    protected $table = 'knowledge_base';

    protected $primaryKey = 'articleid';

    protected $fillable = [
        'articlegroup', 'subject', 'description', 'slug', 'active', 'datecreated',
        'article_order', 'staff_article'
    ];
    public $timestamps = false;

    public function group()
    {
        return $this->belongsTo(KnowledgeBaseGroup::class, 'groupid');
    }
}