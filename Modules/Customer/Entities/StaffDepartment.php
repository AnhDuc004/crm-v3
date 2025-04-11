<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="StaffDepartment"),
 * @OA\Property(property="staffdepartmentid", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="staffid", type="integer", description="Mã nhân viên", example="1"),
 * @OA\Property(property="departmentid", type="integer", description="Mã phòng ban", example="1"),
 * )
 *
 * Class StaffDepartment
 *
 */
class StaffDepartment extends Model
{
    protected $table = 'staff_departments';
    
    protected $primaryKey = 'id';

    protected $fillable = [
        'staff_id', 'department_id'
    ];
    protected $hidden = [
       'created_at', 'updated_at', 'created_by', 'updated_by'
    ];
    public $timestamps = true;
    
}
