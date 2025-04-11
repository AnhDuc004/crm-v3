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
        Schema::create('tik_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('Tên thương hiệu');
            $table->integer('authorized_status')->comment('Trạng thái ủy quyền (2 = ủy quyền, 1 = chưa ủy quyền, 0 = không ủy quyền)');
            $table->boolean('is_t1_brand')->comment('Cờ chỉ ra liệu đây có phải là thương hiệu T1 (True/False)');
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
        Schema::dropIfExists('tik_brands');
    }
};
