<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shp_logistics', function (Blueprint $table) {
            $table->id();
            $table->integer('shp_id')->nullable()->comment('ID vận chuyển từ hệ thống SHP');
            $table->integer('product_id')->comment('ID sản phẩm, khóa ngoại từ bảng sản phẩm');
            $table->float('shipping_fee')->nullable()->comment('Phí vận chuyển của sản phẩm');
            $table->tinyInteger('enabled')->comment('Trạng thái kích hoạt vận chuyển (true: kích hoạt, false: không kích hoạt)');
            $table->tinyInteger('is_free')->default(0)->comment('Chỉ định miễn phí vận chuyển cho người mua hay không');
            $table->integer('size_id')->nullable()->comment('ID kích thước sản phẩm nếu có');
            $table->enum('shipping_fee_type', ['CUSTOM_PRICE', 'SIZE_SELECTION'])
                ->nullable()
                ->charset('utf8mb3')
                ->collation('utf8mb3_general_ci')
                ->comment('Loại phí vận chuyển (giá tùy chỉnh hoặc theo kích thước)');
            $table->integer('created_by')->nullable()->comment('ID người tạo bản ghi');
            $table->integer('updated_by')->nullable()->comment('ID người cập nhật bản ghi');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('shp_products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shp_logistics');
    }
};
