<?php

namespace Modules\Project\Entities;

use App\Models\BaseModel;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Admin\Entities\Staff;
use Modules\Customer\Entities\Tag;
use Modules\Customer\Entities\Taggables;
use Modules\Task\Entities\Task;

/**
 * @OA\Schema(
 *     schema="ProjectModel",
 *     title="Project",
 *     description="Project model",
 *     @OA\Xml(name="Project"),
 *     @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1", readOnly=true),
 *     @OA\Property(property="name", type="string", description="Tên dự án", example="Project Name"),
 *     @OA\Property(property="description", type="string", description="Mô tả", example="Project Description"),
 *     @OA\Property(property="status", type="integer", description="Trạng thái (1: Not Started, 2: In Progress, 3: Completed, etc.)", example="1"),
 *     @OA\Property(property="customer_id", type="integer", description="Mã khách hàng", example="1"),
 *     @OA\Property(property="billing_type", type="integer", description="Loại biên nhận", example="1"),
 *     @OA\Property(property="start_date", type="string", format="date", description="Ngày bắt đầu", example="2024-12-12"),
 *     @OA\Property(property="deadline", type="string", format="date", description="Ngày kết thúc", example="2025-01-15"),
 *     @OA\Property(property="date_finished", type="string", format="date-time", description="Ngày hoàn thành", example="2025-01-20T10:00:00"),
 *     @OA\Property(property="progress", type="integer", description="Tiến độ (0-100)", example="20"),
 *     @OA\Property(property="progress_from_tasks", type="integer", description="Tính toán tiến độ (0: false, 1: true)", example="1"),
 *     @OA\Property(property="project_cost", type="number", format="float", description="Tổng chi phí dự án", example="100.00"),
 *     @OA\Property(property="project_rate_per_hour", type="number", format="float", description="Tiền công mỗi giờ dự án", example="50.00"),
 *     @OA\Property(property="estimated_hours", type="number", format="float", description="Số giờ ước tính", example="2.00"),
 * )
 */
class Project extends BaseModel
{
    protected $table = 'projects';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'description',
        'status',
        'customer_id',
        'billing_type',
        'start_date',
        'deadline',
        'date_finished',
        'progress',
        'progress_from_tasks',
        'project_cost',
        'project_rate_per_hour',
        'estimated_hours'
    ];

    protected $hidden = [
        'updated_at',
        'updated_by',
        'created_at',
        'created_by'
    ];

    public $timestamps = true;

    public function tags()
    {
        return $this->belongsToMany(Tag::class, Taggables::class, 'rel_id', 'tag_id')->where('rel_type', '=', 'project');
    }

    public function taggable()
    {
        return $this->hasMany(Taggables::class, 'rel_id', 'id');
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'project_members', 'project_id', 'staff_id');
    }


    public function milestone()
    {
        return $this->hasMany(ProjectMilestone::class, 'project_id');
    }

    public function task()
    {
        return $this->hasMany(Task::class, 'rel_id')->where('rel_type', '=', 'project');
    }

    public function members()
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     *  Get customfieldsvalues hasMany to project
     */
    public function customFieldsValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_id')->where('customfieldsvalues.field_to', '=', 'projects');
    }

    /**
     *  Get customfields belongsToMany to project
     */
    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, CustomFieldValue::class, 'rel_id', 'field_id')->where('customfieldsvalues.field_to', '=', 'projects');
    }
}
