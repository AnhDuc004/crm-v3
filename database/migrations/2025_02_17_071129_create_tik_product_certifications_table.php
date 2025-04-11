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
        Schema::create('tik_product_certifications', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('Tên chứng nhận (Ví dụ: ISO 9001, CE)');
            $table->json('files')->comment('Các tệp chứng nhận dưới dạng JSON (Danh sách các tệp đính kèm)');
            $table->json('images')->comment('Các hình ảnh chứng nhận dưới dạng JSON');
            $table->string('title', 255)->comment('Tiêu đề chứng nhận (Ví dụ: "Chứng nhận chất lượng")');
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
        Schema::dropIfExists('tik_product_certifications');
    }
};
