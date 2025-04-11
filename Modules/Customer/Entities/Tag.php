<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Lead\Entities\Lead;

/**
 *
 * @OA\Schema(
 * schema="TagModel",
 * @OA\Xml(name="TagModel"),
 * @OA\Property(property="name", type="string", description="Tên", example="abc"),
 * )
 *
 * Class Tag
 *
 */
class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = ['name'];

    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'pivot'
    ];

    public $timestamps = false;

    /**
     *  Get lead belongsTo to tag
     */
    public function leads()
    {
        return $this->belongsToMany(Lead::class, Taggables::class, 'tag_id', 'rel_id')
            ->where('rel_type', '=', 'lead');
    }
}
