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
        Schema::create('inv_warehouses', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID kho');
            $table->string('name', 255)->comment('Tên kho');
            $table->string('location', 255)->nullable()->comment('Địa điểm kho');
            $table->string('warehouse_type', 50)->nullable()->comment('Loại kho (nguyên vật liệu, thành phẩm hoặc cả 2)');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Người tạo bản ghi');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Người cập nhật bản ghi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_warehouses');
    }
};
