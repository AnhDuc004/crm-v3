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
        Schema::create('inv_sales_orders', function (Blueprint $table) {
            $table->id()->comment('ID đơn bán hàng');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade')->comment('ID khách hàng');
            $table->string('order_number')->unique()->comment('Mã đơn bán hàng');
            $table->date('order_date')->comment('Ngày bán hàng');
            $table->decimal('total_amount', 15, 2)->comment('Tổng giá trị đơn hàng');
            $table->string('status', 50)->comment('Trạng thái đơn hàng');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('Người tạo bản ghi');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('Người cập nhật bản ghi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_sales_orders');
    }
};
