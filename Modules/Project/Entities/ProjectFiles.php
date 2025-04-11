<?php

namespace Modules\Project\Entities;

use App\Models\BaseModel;
use App\Utils\Models\User;
use Modules\Customer\Entities\Contact;
use Modules\Admin\Entities\Staff;

/**
 *
 * @OA\Schema(
 * schema="ProjectFileModel",
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="file_name", type="string", description="Tên tệp (độ dài tối đa 255 ký tự)", example="example.txt"),
 * @OA\Property(property="subject", type="string", description="Chủ đề (độ dài tối đa 255 ký tự)", example="Project Subject"),
 * @OA\Property(property="description", type="string", description="Mô tả (độ dài tùy ý)", example="This is a description."),
 * @OA\Property(property="file_type", type="string", description="Loại tệp (độ dài tối đa 50 ký tự)", example="txt"),
 * @OA\Property(property="last_activity", type="string", format="date-time", description="Hoạt động cuối cùng", example="2023-10-02T00:00:00Z"),
 * @OA\Property(property="project_id", type="integer", description="ID dự án", example="1"),
 * @OA\Property(property="visible_to_customer", type="tinyInteger", description="Hiển thị cho khách hàng", example="1"),
 * @OA\Property(property="staff_id", type="integer", description="ID nhân viên", example="1"),
 * @OA\Property(property="contact_id", type="integer", description="ID liên hệ", example="1"),
 * @OA\Property(property="external", type="string", description="Ngoại vi (độ dài tùy ý)", example="External Data"),
 * @OA\Property(property="external_link", type="string", description="Liên kết ngoại vi (độ dài tối đa 255 ký tự)", example="http://example.com"),
 * @OA\Property(property="thumbnail_link", type="string", description="Liên kết hình thu nhỏ (độ dài tối đa 255 ký tự)", example="http://example.com/thumbnail.jpg"),
 * @OA\Property(property="created_by", type="integer", description="ID người tạo", example="1"),
 * @OA\Property(property="updated_by", type="integer", description="ID người sửa", example="1"),
 * required={"id", "file_name", "subject", "file_type", "project_id"}
 *
 * )
 * )
 * Class ProjectFileModel
 *
 */
class ProjectFiles extends BaseModel
{
    protected $table = "project_files";
    protected $primaryKey = 'id';

    protected $fillable = [
        'file_name', 'subject', 'description', 'file_type',
        'last_activity', 'project_id','visible_to_customer',
        'staff_id', 'contact_id', 'external_link','thumbnail_link',
        'created_by', 'updated_by'
    ];

    protected $hidden = [

    ];

    public $timestamps = true;

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function staff() {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
