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
        Schema::create('tik_products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('Tên sản phẩm');
            $table->bigInteger('product_id')->comment('ID sản phẩm trong hệ thống');
            $table->integer('status')->comment('Trạng thái sản phẩm (Ví dụ: 4 - Đang bán, 5 - Ngừng bán...)');
            $table->integer('create_time')->comment('Thời gian tạo sản phẩm (dạng Unix timestamp)');
            $table->integer('update_time')->comment('Thời gian cập nhật sản phẩm (dạng Unix timestamp)');
            $table->integer('total')->comment('Tổng số sản phẩm có sẵn');
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
        Schema::dropIfExists('tik_products');
    }
};
