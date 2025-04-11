<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


/**
 *
 * @OA\Schema(
 * @OA\Xml(name="RoleHasPermissions"),
 * @OA\Property(property="role_id", type="integer", description="Mã role", example="1"),
 * @OA\Property(property="permission_id", type="integer", description="Mã nhóm quyền", example="1"),
 *
 * )
 *
 * Class RoleHasPermissions
 *
 */
class RoleHasPermissions extends BaseModel
{
    protected $table = 'role_has_permissions';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id', 'permission_id'
    ];


}
