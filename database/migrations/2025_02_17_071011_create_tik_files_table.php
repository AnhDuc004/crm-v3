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
        Schema::create('tik_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_id', 255)->comment('ID tệp, dùng để liên kết với hệ thống quản lý tệp');
            $table->string('file_name', 255)->comment('Tên tệp (Ví dụ: "file.pdf")');
            $table->string('file_type', 50)->comment('Loại tệp (Ví dụ: PDF, JPEG, PNG)');
            $table->string('file_url', 255)->comment('URL của tệp (Đường dẫn đến tệp)');
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
        Schema::dropIfExists('tik_files');
    }
};
