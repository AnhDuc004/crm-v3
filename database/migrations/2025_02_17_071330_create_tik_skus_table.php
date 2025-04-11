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
        Schema::create('tik_skus', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sku_id')->comment('ID SKU (dành cho hệ thống quản lý SKU)');
            $table->bigInteger('product_id')->comment('ID sản phẩm liên kết với bảng tik_products');
            $table->string('seller_sku', 255)->comment('SKU của người bán');
            $table->json('price')->comment('Giá sản phẩm dưới dạng JSON (Danh sách các mức giá, giá gốc, giá sau thuế...)');
            $table->json('stock_infos')->comment('Thông tin kho dưới dạng JSON (Số lượng tồn kho, trạng thái kho...)');
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
        Schema::dropIfExists('tik_skus');
    }
};
