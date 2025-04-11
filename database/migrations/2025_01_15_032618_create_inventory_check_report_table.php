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
        Schema::create('inventory_check_report', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->nullable()->constrained('inv_materials')->onDelete('cascade')->comment('ID nguyên vật liệu (nếu là báo cáo nguyên vật liệu)');
            $table->foreignId('product_id')->nullable()->constrained('inv_products')->onDelete('cascade')->comment('ID sản phẩm (nếu là báo cáo thành phẩm)');
            $table->foreignId('warehouse_id')->constrained('inv_warehouses')->onDelete('cascade')->comment('ID kho');
            $table->date('check_date')->comment('Ngày kiểm kho');
            $table->decimal('actual_stock', 15, 2)->comment('Số lượng tồn kho thực tế');
            $table->decimal('stock_difference', 15, 2)->comment('Chênh lệch số lượng tồn kho giữa kiểm kho và báo cáo');
            $table->foreignId('created_by')->nullable()->constrained('users')->comment('Người tạo báo cáo kiểm kho');
            $table->foreignId('updated_by')->nullable()->constrained('users')->comment('Người cập nhật báo cáo kiểm kho');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_check_report');
    }
};
