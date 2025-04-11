<?php
// Modules/Inventory/Entities/ProductionNorm.php
namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ProductionNormModel",
 *     type="object",
 *     title="ProductionNorm",
 *     description="Định mức sản xuất",
 *     required={"product_id", "material_id", "norm_quantity"},
 *     @OA\Property(
 *         property="product_id",
 *         type="integer",
 *         description="ID sản phẩm",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="material_id",
 *         type="integer",
 *         description="ID nguyên vật liệu",
 *         example=2
 *     ),
 *     @OA\Property(
 *         property="norm_quantity",
 *         type="number",
 *         format="float",
 *         description="Số lượng nguyên vật liệu cần để sản xuất 1 sản phẩm",
 *         example=10.5
 *     ),
 *     @OA\Property(
 *         property="season",
 *         type="string",
 *         description="Mùa sản xuất",
 *         example="Mùa hè"
 *     ),
 * )
 */
class ProductionNorm extends Model
{
    protected $table = 'inv_production_norms';
    protected $fillable = ['product_id', 'material_id', 'norm_quantity', 'season',];

    protected $hidden = ['created_at', 'updated_at', 'created_by', 'updated_by'];

    public $timestamps = true;

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
