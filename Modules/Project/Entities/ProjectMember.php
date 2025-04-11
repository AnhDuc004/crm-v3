<?php

namespace Modules\Project\Entities;

use App\Models\BaseModel;
use App\Utils\Models\User;
use Modules\Admin\Entities\Staff;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="ProjectMemberModel"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="project_id", type="integer", description="Mã dự án", example="1"),
 * @OA\Property(property="staff_id", type="integer", description="Mã thành viên", example="1")
 *
 * )
 *
 * Class ProjectMemberModel
 *
 */
class ProjectMember extends BaseModel
{
    protected $table = "project_members";
    protected $primaryKey = 'id';
    protected $fillable = [
        'project_id', 'staff_id'
    ];
    public $timestamps = false;

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
