<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * @OA\Xml(name="Vault"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="customer_id", type="integer", description="Mã khách hàng", example="1"),
 * @OA\Property(property="server_address", type="string", description="Địa chỉ server", example="127.0.0.1:8000"),
 * @OA\Property(property="post", type="integer", description="Cổng", example="8000"),
 * @OA\Property(property="username", type="string", description="Tên người đăng nhập", example="abc"),
 * @OA\Property(property="password", type="string", description="Mật khẩu", example="12345678"),
 * @OA\Property(property="description", type="string", description="Mô tả", example="abc"),
 * @OA\Property(property="creator", type="integer", description="Người sáng tạo", example="1"),
 * @OA\Property(property="creator_name", type="string", description="Mô tả", example="abc"),
 * @OA\Property(property="visibility", type="integer", description="Hiển thị", example="0.false, 1.true"),
 * @OA\Property(property="share_in_projects", type="integer", description="Chia sẻ dự án", example="0.false, 1.true"),
 * @OA\Property(property="last_updated", type="date", description="Ngày cập nhật cuối", example="1/4/2021"),
 * @OA\Property(property="last_updated_from", type="string", description="Được cập nhật bởi", example="abc"),
 * @OA\Property(property="date_created", type="date", description="Ngày tạo", example="4/6/2021"),
 * )
 *
 * Class Vault
 *
 */
class Vault extends BaseModel
{
    protected $table = 'vault';

    protected $fillable = [
        'customer_id', 'server_address', 'port', 'username',  'password', 'description',
        'creator', 'creator_name', 'visibility', 'share_in_projects', 'last_updated', 'last_updated_from',
        'date_created'
    ];
    public $timestamps = false;

    /**
     *  Get customer belongsTo to vault
    */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}