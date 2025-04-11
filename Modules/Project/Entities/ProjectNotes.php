<?php

namespace Modules\Project\Entities;

use App\Models\BaseModel;
use Modules\Admin\Entities\Staff;

/**
 * @OA\Schema(
 * schema="ProjectNote",
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="project_id", type="integer", description="Mã dự án", example="101"),
 * @OA\Property(property="staff_id", type="integer", description="Mã nhân viên", example="15"),
 * @OA\Property(property="content", type="string", description="Nội dung ghi chú", example="Ghi chú quan trọng về dự án")
 * )
 */

class ProjectNotes extends BaseModel
{
    protected $table = "project_notes";
    protected $primaryKey = 'id';
    protected $fillable = [
        'content'
    ];
    protected $hidden = [
        'project_id',
        'staff_id'
    ];
    public $timestamps = false;

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

}
