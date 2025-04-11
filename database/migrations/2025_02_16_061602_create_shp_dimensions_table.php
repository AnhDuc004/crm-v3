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
        Schema::create('shp_dimensions', function (Blueprint $table) {
            $table->id();
            $table->integer('shp_id')->nullable()->comment('ID kích thước từ hệ thống SHP');
            $table->foreignId('product_id')->constrained('products')->comment('ID sản phẩm, khóa ngoại từ bảng sản phẩm');
            $table->integer('package_width')->comment('Chiều rộng bao bì (đơn vị cm)');
            $table->integer('package_length')->comment('Chiều dài bao bì (đơn vị cm)');
            $table->integer('package_height')->comment('Chiều cao bao bì (đơn vị cm)');
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
        Schema::dropIfExists('shp_dimensions');
    }
};
