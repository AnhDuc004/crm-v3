<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="ModeHasRole"),
 * @OA\Property(property="role_id", type="integer", description="Mã role", example="1"),
 * @OA\Property(property="model_id", type="integer", description="Mã user", example="1"),
 * @OA\Property(property="model_type", type="string", description="Đường dẫn", example="App\User"),
 *
 * )
 *
 * Class ModeHasRole
 *
 */
class ModeHasRole extends Model
{
    protected $table = 'model_has_roles';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id', 'model_type', 'model_id'
    ];


}
