<?php

namespace Modules\Sale\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Admin\Entities\Staff;

/**
 * @OA\Schema(
 *     schema="ProposalComment",
 *     type="object",
 *     required={"content", "proposal_id", "staff_id"},
 *     @OA\Property(property="id", type="integer", example=1, description="ID của bình luận đề xuất"),
 *     @OA\Property(property="content", type="string", example="This is a proposal comment", description="Nội dung bình luận"),
 *     @OA\Property(property="proposal_id", type="integer", example=123, description="ID của đề xuất mà bình luận này thuộc về"),
 *     @OA\Property(property="staff_id", type="integer", example=456, description="ID của nhân viên đã bình luận"),
 * )
 */
class ProposalComments extends Model
{
    protected $table = 'proposal_comments';
    protected $primaryKey = 'id';
    protected $fillable = [
        'content',
        'proposal_id',
        'staff_id'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];
    public $timestamps = true;
}
