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
        Schema::create('inv_stock_report', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->nullable()->constrained('inv_materials')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('inv_products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('inv_warehouses')->onDelete('cascade');
            $table->decimal('total_in', 15, 2);
            $table->decimal('total_out', 15, 2);
            $table->decimal('stock_balance', 15, 2);
            $table->decimal('actual_stock', 15, 2);
            $table->decimal('stock_difference', 15, 2);
            $table->timestamp('report_date')->useCurrent();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_stock_report');
    }
};
