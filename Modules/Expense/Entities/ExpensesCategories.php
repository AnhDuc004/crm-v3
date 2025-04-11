<?php

namespace Modules\Expense\Entities;

use App\Models\BaseModel;

/**
 * @OA\Schema(
 *     schema="ExpensesCategoryModel",
 *     @OA\Xml(name="ExpensesCategoryModel"),
 *     @OA\Property(property="id", type="integer", description="Mã hệ thống tự sinh", example="1"),
 *     @OA\Property(property="name", type="string", description="Tên danh mục chi phí", example="Vận chuyển"),
 *     @OA\Property(property="description", type="string", description="Mô tả danh mục chi phí", example="Chi phí vận chuyển hàng hóa")
 * )
 */

class ExpensesCategories extends BaseModel
{
    protected $table = 'expenses_categories';

    protected $fillable = ['name', 'description'];
    public $timestamps = false;
}
