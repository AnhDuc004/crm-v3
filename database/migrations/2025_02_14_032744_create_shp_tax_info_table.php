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
        Schema::create('shp_tax_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shp_id')->nullable()->constrained('shps'); // Liên kết với bảng SHP
            $table->foreignId('product_id')->constrained('products'); // Liên kết với bảng sản phẩm
            $table->string('ncm', 8)->nullable(); // Mã số thuế sản phẩm
            $table->enum('tax_type', ['TAXABLE', 'NON_TAXABLE'])->default('NON_TAXABLE'); // Loại thuế
            $table->float('tax_rate')->nullable(); // Tỷ lệ thuế
            $table->foreignId('created_by')->nullable()->constrained('users'); // ID người tạo
            $table->foreignId('updated_by')->nullable()->constrained('users'); // ID người cập nhật
            $table->timestamps();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('shp_products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shp_tax_info');
    }
};
