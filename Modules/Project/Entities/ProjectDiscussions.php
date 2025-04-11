<?php

namespace Modules\Project\Entities;

use App\Models\BaseModel;
use Modules\Customer\Entities\Contact;
use Modules\Admin\Entities\Staff;

/**
 *
 * @OA\Schema(
 *      schema="ProjectDiscussionsModel",
 *      @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 *      @OA\Property(property="project_id", type="integer", description="Mã dự án", example="1"),
 *      @OA\Property(property="description", type="string", description="Mô tả", example="abc"),
 *      @OA\Property(property="subject", type="string", description="Chủ đề", example="abc"),
 *      @OA\Property(property="staff_id", type="integer", description="Mã thành viên", example="1"),
 *      @OA\Property(property="contact_id", type="integer", description="Mã liên hệ", example="1"),
 *      @OA\Property(property="show_to_customer", type="integer", description="Hiển thị cho khách hàng", example="1.true, 0.false"),
 *      @OA\Property(property="created_by", type="integer", description="Mã người tạo", example="1"),
 *      @OA\Property(property="updated_by", type="integer", description="Mã người sửa", example="1"),
 * )
 * Class ProjectDiscussions
 */
class ProjectDiscussions extends BaseModel
{
    protected $table = 'project_discussions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'project_id', 'description', 'subject', 'show_to_customer', 'staff_id',
        'contact_id','created_by','updated_by'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
}
