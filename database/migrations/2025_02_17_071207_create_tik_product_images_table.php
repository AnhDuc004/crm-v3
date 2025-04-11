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
        Schema::create('tik_product_images', function (Blueprint $table) {
            $table->id();
            $table->json('url_list')->comment('Các URL hình ảnh dưới dạng JSON (Danh sách các URL)');
            $table->json('thumb_url_list')->comment('Các URL của ảnh thumbnail dưới dạng JSON');
            $table->integer('height')->comment('Chiều cao của hình ảnh');
            $table->integer('width')->comment('Chiều rộng của hình ảnh');
            $table->bigInteger('product_id')->comment('ID sản phẩm liên kết với bảng tik_products');
            $table->integer('created_by')->comment('ID người tạo bản ghi');
            $table->integer('updated_by')->comment('ID người cập nhật bản ghi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tik_product_images');
    }
};
