<?php

namespace Modules\Tik\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TikFile",
 *     title="TikFile",
 *     description="File model representing uploaded files",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID tệp, khóa chính, định danh duy nhất của tệp", example=1),
 *     @OA\Property(property="file_id", type="string", description="ID tệp, dùng để liên kết với hệ thống quản lý tệp", example="file_123456"),
 *     @OA\Property(property="file_name", type="string", description="Tên tệp (Ví dụ: 'file.pdf')", example="product_spec.pdf"),
 *     @OA\Property(property="file_type", type="string", description="Loại tệp (Ví dụ: PDF, JPEG, PNG)", example="PDF"),
 *     @OA\Property(property="file_url", type="string", description="URL của tệp (Đường dẫn đến tệp)", example="https://example.com/files/product_spec.pdf"),
 *     @OA\Property(property="created_by", type="integer", description="ID người tạo bản ghi", example=1),
 *     @OA\Property(property="updated_by", type="integer", description="ID người cập nhật bản ghi", example=1),
 * )
 */
class TikFile extends Model
{
    protected $table = 'tik_files';

    protected $fillable = [
        'file_id',
        'file_name',
        'file_type',
        'file_url',
        'created_by',
        'updated_by',
    ];
    public $timestamps = true;
}
