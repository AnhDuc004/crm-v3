<?php

namespace Modules\Shp\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ShpProduct",
 *     type="object",
 *     required={"product_name", "category_id", "current_price"},
 *     @OA\Property(property="id", type="integer", example=1, description="ID sản phẩm"),
 *     @OA\Property(property="shp_id", type="integer", nullable=true, example=1001, description="ID sản phẩm từ hệ thống SHP"),
 *     @OA\Property(property="product_name", type="string", maxLength=255, example="Nike Air Max", description="Tên sản phẩm"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Giày thể thao Nike", description="Mô tả chi tiết sản phẩm"),
 *     @OA\Property(property="weight", type="number", format="float", example=1.2, description="Trọng lượng sản phẩm (kg)"),
 *     @OA\Property(property="product_status", type="string", enum={"NORMAL", "UNLIST"}, example="NORMAL", description="Trạng thái sản phẩm"),
 *     @OA\Property(property="price_info", type="object", example={"currency": "VND", "amount": 1000000}, description="Thông tin giá sản phẩm"),
 *     @OA\Property(property="current_price", type="number", format="float", example=950000, description="Giá hiện tại của sản phẩm"),
 *     @OA\Property(property="original_price", type="number", format="float", nullable=true, example=1000000, description="Giá gốc của sản phẩm"),
 *     @OA\Property(property="condition", type="string", enum={"NEW", "USED"}, example="NEW", description="Tình trạng sản phẩm"),
 *     @OA\Property(property="category_id", type="integer", example=5, description="ID danh mục sản phẩm"),
 *     @OA\Property(property="logistic_info", type="object", example={"shipping_method": "express", "delivery_time": "2-3 days"}, description="Thông tin vận chuyển"),
 *     @OA\Property(property="description_type", type="string", enum={"normal", "extended"}, example="normal", description="Loại mô tả sản phẩm"),
 *     @OA\Property(property="video_info", type="object", example={"url": "https://example.com/video.mp4"}, description="Thông tin video sản phẩm"),
 *     @OA\Property(property="product_dangerous", type="integer", example=0, description="Chỉ số sản phẩm nguy hiểm"),
 *     @OA\Property(property="brand_id", type="integer", nullable=true, example=3, description="ID thương hiệu sản phẩm"),
 *     @OA\Property(property="gtin_code", type="string", nullable=true, maxLength=14, example="01234567890123", description="Mã GTIN của sản phẩm"),
 *     @OA\Property(property="extended_description", type="object", example={"detail": "Thông tin chi tiết sản phẩm"}, description="Mô tả mở rộng sản phẩm"),
 *     @OA\Property(property="complaint_policy", type="object", example={"return_policy": "14 ngày hoàn trả"}, description="Chính sách khiếu nại sản phẩm"),
 *     @OA\Property(property="warranty_time", type="string", enum={"ONE_YEAR", "TWO_YEARS", "OVER_TWO_YEARS"}, example="ONE_YEAR", description="Thời gian bảo hành"),
 *     @OA\Property(property="exclude_entrepreneur_warranty", type="boolean", nullable=true, example=true, description="Loại trừ bảo hành cho doanh nghiệp"),
 *     @OA\Property(property="complaint_address_id", type="integer", nullable=true, example=42, description="ID địa chỉ khiếu nại sản phẩm"),
 *     @OA\Property(property="additional_information", type="string", nullable=true, example="Hàng chính hãng", description="Thông tin bổ sung"),
 *     @OA\Property(property="seller_stock", type="object", example={"stock": 100, "warehouse": "Hà Nội"}, description="Thông tin kho hàng của người bán"),
 *     @OA\Property(property="scheduled_publish_time", type="string", format="date-time", nullable=true, example="2025-02-20T10:00:00Z", description="Thời gian công bố sản phẩm"),
 *     @OA\Property(property="authorised_brand_id", type="integer", nullable=true, example=7, description="ID thương hiệu được ủy quyền"),
 *     @OA\Property(property="created_by", type="integer", nullable=true, example=26, description="ID người tạo"),
 *     @OA\Property(property="updated_by", type="integer", nullable=true, example=30, description="ID người cập nhật"),
 * )
 */
class ShpProduct extends Model
{
    use HasFactory;

    protected $table = 'shp_products';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'shp_id',
        'product_name',
        'description',
        'weight',
        'product_status',
        'price_info',
        'current_price',
        'original_price',
        'condition',
        'category_id',
        'logistic_info',
        'description_type',
        'video_info',
        'product_dangerous',
        'brand_id',
        'gtin_code',
        'extended_description',
        'complaint_policy',
        'warranty_time',
        'exclude_entrepreneur_warranty',
        'complaint_address_id',
        'additional_information',
        'seller_stock',
        'scheduled_publish_time',
        'authorised_brand_id',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'price_info' => 'array',
        'logistic_info' => 'array',
        'video_info' => 'array',
        'extended_description' => 'array',
        'complaint_policy' => 'array',
        'seller_stock' => 'array',
        'scheduled_publish_time' => 'datetime'
    ];
    const PRODUCT_STATUS_NORMAL = 'NORMAL';
    const PRODUCT_STATUS_UNLIST = 'UNLIST';

    const CONDITION_NEW = 'NEW';
    const CONDITION_USED = 'USED';

    const WARRANTY_ONE_YEAR = 'ONE_YEAR';
    const WARRANTY_TWO_YEARS = 'TWO_YEARS';
    const WARRANTY_OVER_TWO_YEARS = 'OVER_TWO_YEARS';

    const DESCRIPTION_TYPE_NORMAL = 'normal';
    const DESCRIPTION_TYPE_EXTENDED = 'extended';

    public function category()
    {
        return $this->belongsTo(ShpCategory::class, 'category_id');
    }

    public function attributes()
    {
        return $this->hasMany(ShpAttribute::class, 'product_id');
    }

    public function dimensions()
    {
        return $this->hasMany(ShpDimension::class, 'product_id');
    }

    public function gtins()
    {
        return $this->hasMany(ShpGtin::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ShpImgae::class, 'product_id');
    }

    public function logistics()
    {
        return $this->hasMany(ShpLogistic::class, 'product_id');
    }

    public function preorders()
    {
        return $this->hasMany(ShpPreorder::class, 'product_id');
    }

    public function sellerStocks()
    {
        return $this->hasMany(ShpSellerStock::class, 'product_id');
    }

    public function taxInfo()
    {
        return $this->hasOne(ShpTaxInfo::class, 'product_id');
    }

    public function videos()
    {
        return $this->hasMany(ShpVideo::class, 'product_id');
    }

    public function wholesales()
    {
        return $this->hasMany(ShpWholesale::class, 'product_id');
    }
}
