<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;

/**
 *
 * @OA\Schema(
 * schema="CustomFieldModel",
 * @OA\Xml(name="CustomFieldModel"),
 * @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 * @OA\Property(property="field_to", type="string", description="Lĩnh vực cho", example="customers"),
 * @OA\Property(property="name", type="string", description="Tên lĩnh vực", example="Abc"),
 * @OA\Property(property="slug", type="string", description="Nội dung", example="bhome-fe"),
 * @OA\Property(property="required", type="integer", description="Bắt buộc", example="0.false, 1.true"),
 * @OA\Property(property="type", type="string", description="Kiểu lĩnh vực", example="input"),
 * @OA\Property(property="options", type="string", description="Lựa chọn", example="1.a, 2.b, 3.c"),
 * @OA\Property(property="display_inline", type="integer", description="Hiển thị không", example="0.false, 1.true"),
 * @OA\Property(property="field_order", type="integer", description="Vị trí ưu tiên", example="1"),
 * @OA\Property(property="active", type="integer", description="Hoạt động", example="0.false, 1.true"),
 * @OA\Property(property="show_on_pdf", type="integer", description="Hiển thị pdf", example="0.false, 1.true"),
 * @OA\Property(property="show_on_ticket_form", type="integer", description="Hiển thị trên mẫu vé", example="0.false, 1.true"),
 * @OA\Property(property="only_admin", type="integer", description="Chỉ có admin", example="0.false, 1.true"),
 * @OA\Property(property="show_on_table", type="integer", description="Hiển thị bảng", example="0.false, 1.true"),
 * @OA\Property(property="show_on_client_portal", type="integer", description="Hiển thị trên cổng thông tin khách hàng", example="0.false, 1.true"),
 * @OA\Property(property="disalow_client_to_edit", type="integer", description="Không cho khách hàng chỉnh sửa", example="0.false, 1.true"),
 * @OA\Property(property="bs_column", type="integer", description="Chia các cột", example="10"),
 * )
 *
 * Class CustomField
 *
 */
class CustomField extends BaseModel
{
    protected $table = 'customfields';
    protected $primaryKey = 'id';
    protected $fillable = [
        'field_to',
        'name',
        'slug',
        'required',
        'type',
        'options',
        'display_inline',
        'field_order',
        'active',
        'show_on_pdf',
        'show_on_ticket_form',
        'only_admin',
        'show_on_table',
        'show_on_client_portal',
        'disalow_client_to_edit',
        'bs_column'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'pivot'
    ];
    public $timestamps = true;
    /**
     *  Get customfieldsvalues hasMany to customfields
     */
    public function customFieldValue()
    {
        return $this->hasMany(CustomFieldValue::class, 'field_id');
    }
}
