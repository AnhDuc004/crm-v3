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
        Schema::create('inv_production_norms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->comment('Sản phẩm');
            $table->unsignedBigInteger('material_id')->comment('Nguyên vật liệu');
            $table->decimal('norm_quantity', 15, 2)->comment('Số lượng nguyên vật liệu cần để sản xuất 1 sản phẩm');
            $table->string('season', 50)->nullable()->comment('Mùa (nếu có phân chia theo mùa)');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Người tạo bản ghi');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Người cập nhật bản ghi');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('inv_products')->onDelete('cascade');
            $table->foreign('material_id')->references('id')->on('inv_materials')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_production_norms');
    }
};
