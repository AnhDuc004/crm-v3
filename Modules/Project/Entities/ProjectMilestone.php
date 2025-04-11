<?php

namespace Modules\Project\Entities;

use App\Models\BaseModel;
use App\Utils\Models\User;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="ProjectMilestone"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="name", type="string", description="Tên cột mốc của dự án", example="1"),
 * @OA\Property(property="description", type="string", description="Mô tả", example="a"),
 * @OA\Property(property="description_visible_to_customer", type="integer", description="Hiển thị mô tả đối với khách hàng", example="1.true, 0.false"),
 * @OA\Property(property="project_id", type="integer", description="Mã dự án", example="1"),
 * @OA\Property(property="due_date", type="date", description="Ngày chốt", example="8/3/2021"),
 * @OA\Property(property="color", type="string", description="Màu sắc", example="#ffffff"),
 * @OA\Property(property="milestone_order", type="integer", description="Thứ tự", example="1"),
 *
 * )
 *
 * Class ProjectMilestone
 *
 */
class ProjectMilestone extends BaseModel
{
    protected $table = "milestones";
    protected $primaryKey = 'id';
    protected $fillable = [
        'name', 'description', 'description_visible_to_customer', 'due_date',
        'project_id', 'color', 'milestone_order'
    ];
    protected $hidden = [
        'datecreated'
    ];
    public $timestamps = false;

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
