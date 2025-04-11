<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inv_inventory_transactions', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID giao dịch kho');
            $table->string('transaction_type', 50)->comment('Loại giao dịch (nhập, xuất)');
            $table->unsignedBigInteger('material_id')->nullable()->comment('Nguyên vật liệu liên quan (nếu có)');
            $table->unsignedBigInteger('product_id')->nullable()->comment('Sản phẩm liên quan (nếu có)');
            $table->decimal('quantity', 15, 2)->comment('Số lượng giao dịch (hỗ trợ số nguyên và số thập phân)');
            $table->unsignedBigInteger('warehouse_id')->comment('ID kho');
            $table->timestamp('transaction_date')->default( DB::raw('CURRENT_TIMESTAMP'))->comment('Ngày giao dịch');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Người tạo giao dịch');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Người cập nhật giao dịch');
            $table->timestamps();

            // 
            $table->foreign('material_id')->references('id')->on('inv_materials')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('inv_products')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('inv_warehouses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_inventory_transactions');
    }
};
