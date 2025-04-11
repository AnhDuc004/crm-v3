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
        Schema::create('inv_units', function (Blueprint $table) {
            $table->id()->comment('ID đơn vị tính');
            $table->string('name')->comment('Tên đơn vị tính');
            $table->bigInteger('created_by')->nullable()->comment('Người tạo bản ghi');
            $table->bigInteger('updated_by')->nullable()->comment('Người cập nhật bản ghi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_units');
    }
};
