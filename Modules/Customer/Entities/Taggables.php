<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="Taggables"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="rel_id", type="integer", description="Mã rel", example="1"),
 * @OA\Property(property="rel_type", type="string", description="Tên rel", example="project"),
 * @OA\Property(property="tag_id", type="integer", description="Mã tag", example="1"),
 * @OA\Property(property="tag_order", type="integer", description="Vị trí tag", example="1"),
 * )
 *
 * Class Taggables
 *
 */
class Taggables extends Model
{

    protected $table = 'taggables';

    protected $fillable = ['rel_id', 'rel_type', 'tag_id', 'tag_order'];

    public $timestamps = false;
}
