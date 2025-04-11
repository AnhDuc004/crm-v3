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
        Schema::create('tik_categories', function (Blueprint $table) {
            $table->id();
            $table->string('local_display_name', 255)->comment('Tên hiển thị địa phương của danh mục');
            $table->bigInteger('parent_id')->nullable()->comment('ID danh mục cha (Nếu có), liên kết với id của bảng này');
            $table->boolean('is_leaf')->comment('Liệu danh mục này có phải là danh mục lá (Không có danh mục con)');
            $table->json('status')->comment('Trạng thái của danh mục dưới dạng JSON (Ví dụ: {"active": true})');
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
        Schema::dropIfExists('tik_categories');
    }
};
