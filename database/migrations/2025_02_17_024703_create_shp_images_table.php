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
        Schema::create('shp_images', function (Blueprint $table) {
            $table->id();
            $table->integer('shp_id')->nullable()->comment('ID hình ảnh từ hệ thống SHP');
            $table->integer('product_id')->comment('ID sản phẩm, khóa ngoại từ bảng sản phẩm');
            $table->string('image_url', 255)->charset('utf8mb3')->collation('utf8mb3_general_ci')->comment('URL của hình ảnh sản phẩm');
            $table->enum('image_ratio', ['1:1', '3:4'])->charset('utf8mb3')->collation('utf8mb3_general_ci')->default('1:1')->nullable()->comment('Tỷ lệ hình ảnh (1:1 hoặc 3:4)');
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
        Schema::dropIfExists('shp_images');
    }
};
