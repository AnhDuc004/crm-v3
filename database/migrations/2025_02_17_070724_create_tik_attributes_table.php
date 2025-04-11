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
        Schema::create('tik_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('Tên thuộc tính (Ví dụ: Màu sắc, Kích thước...)');
            $table->integer('attribute_type')->comment('Loại thuộc tính (Ví dụ: 1 cho "Text", 2 cho "Number", 3 cho "Date"...)');
            $table->string('value_data_format', 255)->comment('Định dạng dữ liệu cho giá trị thuộc tính (Ví dụ: "String", "Integer", "Date"...)');
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
        Schema::dropIfExists('tik_attributes');
    }
};
