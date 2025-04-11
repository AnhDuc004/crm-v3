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
        Schema::create('inv_products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Tên sản phẩm');
            $table->text('description')->nullable()->comment('Mô tả sản phẩm');
            $table->unsignedBigInteger('unit_id')->comment('Đơn vị tính của sản phẩm');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Người tạo bản ghi');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Người cập nhật bản ghi');
            $table->timestamps();

            // 
            $table->foreign('unit_id')->references('id')->on('inv_units')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_products');
    }
};
