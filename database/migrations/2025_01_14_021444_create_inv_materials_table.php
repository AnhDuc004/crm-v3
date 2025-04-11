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
        Schema::create('inv_materials', function (Blueprint $table) {
            $table->id()->comment('ID nguyên vật liệu');
            $table->string('name', 255)->comment('Tên nguyên vật liệu');
            $table->text('description')->nullable()->comment('Mô tả nguyên vật liệu');
            $table->foreignId('unit_id')->comment('Đơn vị tính của nguyên vật liệu')
                ->constrained('inv_units')->onDelete('restrict');
            $table->foreignId('supplier_id')->comment('Nhà cung cấp của nguyên vật liệu')
                ->constrained('inv_suppliers')->onDelete('restrict');
            $table->foreignId('created_by')->nullable()->comment('Người tạo bản ghi');
            $table->foreignId('updated_by')->nullable()->comment('Người cập nhật bản ghi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_materials');
    }
};
