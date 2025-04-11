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
        Schema::create('tik_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('attribute_id')->comment('ID thuộc tính (liên kết với bảng tik_attributes)');
            $table->string('name', 255)->comment('Tên của giá trị thuộc tính (Ví dụ: "Đỏ", "XL"...)');
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
        Schema::dropIfExists('tik_attribute_values');
    }
};
