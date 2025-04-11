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
        Schema::create('tik_product_sales_attributes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->comment('ID sản phẩm liên kết với bảng tik_products');
            $table->bigInteger('attribute_id')->comment('ID thuộc tính liên kết với bảng tik_attributes');
            $table->bigInteger('value_id')->comment('ID giá trị thuộc tính liên kết với bảng tik_attribute_values');
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
        Schema::dropIfExists('tik_product_sales_attributes');
    }
};
