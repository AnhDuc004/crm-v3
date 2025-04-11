<?php

namespace Modules\Admin\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @OA\Schema(
 * schema="CurrenciesModel",
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="symbol", type="string", description="Kí hiệu (tối đa 10 ký tự)", example="$"),
 * @OA\Property(property="name", type="string", description="Tên (tối đa 100 ký tự)", example="USD"),
 * @OA\Property(property="decimal_separator", type="string", description="Dấu chấm (tối đa 5 ký tự)", example="ghi"),
 * @OA\Property(property="thousand_separator", type="string", description="Dấu phẩy(tối đa 5 ký tự)", example="loi"),
 * @OA\Property(property="placement", type="string", description="Dấu phẩy(tối đa 10 ký tự)", example="loi"),
 * @OA\Property(property="is_default", type="integer", description="Mặc định(tối đa 1 ký tự)", example="1"),
 * required={"name", "is_default", "symbol"},
 *
 * )
 *
 * Class Currencies
 *
 */
class Currencies extends Model
{
    protected $table = 'currencies';

    protected $primaryKey = 'id';

    protected $fillable = [
        'symbol',
        'name',
        'decimal_separator',
        'thousand_separator',
        'placement',
        'is_default'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];
    public $timestamps = true;
}
