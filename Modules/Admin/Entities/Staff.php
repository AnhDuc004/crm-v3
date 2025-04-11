<?php

namespace Modules\Admin\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Customer\Entities\StaffDepartment;
use Modules\Project\Entities\Project;
use Modules\Task\Entities\Task;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

/**
 *
 * @OA\Schema(
 * schema="StaffModel",
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="email", type="string", description="Email", example="abc@gmail.com"),
 * @OA\Property(property="first_name", type="string", description="Họ", example="Nguyễn"),
 * @OA\Property(property="last_name", type="string", description="TÊn", example="An"),
 * @OA\Property(property="domainId", type="integer", description="Id miền", example="1"),
 * @OA\Property(property="facebook", type="string", description="Facebook", example="ABC"),
 * @OA\Property(property="linkedin", type="string", description="Linkedin", example="abc"),
 * @OA\Property(property="phone_number", type="string", description="SĐT", example="0946547334"),
 * @OA\Property(property="skype", type="string", description="Skype", example="abc"),
 * @OA\Property(property="password", type="string", description="Skype", example="abc123"),
 * @OA\Property(property="profile_image", type="string", description="Đường dẫn ảnh", example="abc.jpg"),
 * @OA\Property(property="last_ip", type="string", description="IP cuối cùng", example="127.0.0.1"),
 * @OA\Property(property="last_login", type="datetime", description="đăng nhập lần cuối", example="2024-10-24"),
 * @OA\Property(property="last_activity", type="datetime", description="hoạt động cuối cùng", example="2024-11-22"),
 * @OA\Property(property="last_password_change", type="datetime", description="thay đổi mật khẩu lần cuối", example="2024-11-22"),
 * @OA\Property(property="new_pass_key", type="datetime", description="mã_mật_khẩu_mới", example="2024-11-22"),
 * @OA\Property(property="new_pass_key_requested", type="datetime", description="yêu cầu mã khóa mới", example="2024-11-22"),
 * @OA\Property(property="admin", type="integer", description="Admin", example="1"),
 * @OA\Property(property="role", type="integer", description="Phân quyền", example="1"),
 * @OA\Property(property="active", type="integer", description="Hoạt động(0.false, 1.true)", example="1"),
 * @OA\Property(property="default_language", type="string", description="Ngôn ngữ", example="English"),
 * @OA\Property(property="direction", type="string", description="phương hướng", example="đông bắc"),
 * @OA\Property(property="media_path_slug", type="string", description="đường dẫn phương tiện", example="đường hai"),
 * @OA\Property(property="is_not_staff", type="integer", description="Không phải là nhân viên(0.false, 1.true)", example="1"),
 * @OA\Property(property="hourly_rate", type="decimal", format="double", description="Tỷ lệ hàng giờ", example="5.00"),
 * @OA\Property(property="two_factor_auth_enabled", type="tinyInteger", description="xác thực hai yếu tố được bật", example="1"),
 * @OA\Property(property="two_factor_auth_code", type="string", description="mã xác thực hai yếu tố", example="kklsd"),
 * @OA\Property(property="two_factor_auth_code_requested", type="dateTime", description="yêu cầu mã xác thực hai yếu tố", example="Nhân viên"),
 * @OA\Property(property="email_signature", type="string", description="chữ ký email", example="Nhân viên"),
 * )
 *
 * Class Staff
 *
 */
class Staff extends Model
{
    protected $table = 'staff';

    use HasRoles;
    use HasPermissions;

    protected $primaryKey = 'id';

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'facebook',
        'linkedin',
        'phone_number',
        'skype',
        'profile_image',
        'email_signature',
        'admin',
        'active',
        'default_language',
        'direction',
        'is_not_staff',
        'hourly_rate',
        'user_id',
    ];
    protected $hidden = [
        'role',
        'password',
        'last_ip',
        'last_login',
        'media_path_slug',
        'two_factor_auth_enabled',
        'two_factor_auth_code',
        'two_factor_auth_code_requested',
        'last_activity',
        'lass_password_change',
        'new_pass_key',
        'new_pass_key_requested',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'pivot'
    ];
    public $timestamps = true;

    /**
     *  Get department hasMany to staff
     */
    public function department()
    {
        return $this->belongsToMany(Department::class, StaffDepartment::class, 'staff_id', 'department_id');
    }

    /**
     *  Get customfieldsvalues hasMany to staff
     */
    public function customFieldsValues()
    {
        return $this->hasMany(CustomFieldValue::class, 'rel_id')->where('customfieldsvalues.field_to', '=', 'staff');
    }

    /**
     *  Get customfields belongsToMany to staff
     */
    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, CustomFieldValue::class, 'rel_id', 'field_id')->where('customfieldsvalues.field_to', '=', 'staff');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_assigned', 'staff_id', 'task_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_members', 'staff_id', 'project_id');
    }
}
