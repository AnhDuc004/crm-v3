<?php

namespace Modules\Admin\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="Countries"),
 * @OA\Property(property="country_id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="iso2", type="string", description="Kí hiệu", example="ZW"),
 * @OA\Property(property="short_name", type="string", description="Tên ngắn", example="Zimbabwe"),
 * @OA\Property(property="long_name", type="string", description="Tên đầy đủ", example="Republic of Zimbabw"),
 * 
 *
 * )
 *
 * Class Countries
 *
 */
class Countries extends Model
{
    protected $table = 'countries';

    protected $primaryKey = 'id';

    protected $fillable = ['iso2', 'short_name', 'long_name'];

    protected $hidden = [
        'created_at', 'updated_at', 'created_by', 'updated_by',
        'iso3', 'numcode', 'un_member', 'calling_code', 'cctld'
    ];
    public $timestamps = true;
}