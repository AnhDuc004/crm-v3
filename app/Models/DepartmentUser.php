<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Worker\Entities\Department;

class DepartmentUser extends Model
{
    public $table = 'department_users';
    public $fillable = [
        'department_id', 'user_id'
    ];

    protected $hidden = ['created_at', 'updated_at','created_by', 'updated_by'];
    public $timestamps = true;

    public function departments()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
