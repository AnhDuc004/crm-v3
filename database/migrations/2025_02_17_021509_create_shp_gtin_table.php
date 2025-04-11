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
        Schema::create('gtins', function (Blueprint $table) {
            $table->increments('id')->comment('ID mã GTIN, khóa chính');
            $table->integer('shp_id')->nullable()->comment('ID mã GTIN từ hệ thống SHP');
            $table->integer('product_id')->comment('ID sản phẩm, khóa ngoại từ bảng sản phẩm');
            $table->string('gtin_code', 14)->comment('Mã GTIN của sản phẩm');
            $table->integer('created_by')->nullable()->comment('ID người tạo bản ghi');
            $table->integer('updated_by')->nullable()->comment('ID người cập nhật bản ghi');
            $table->timestamps();
            // Foreign key constraints
            $table->foreign('product_id')->references('id')->on('shp_products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shp_gtin');
    }
};
