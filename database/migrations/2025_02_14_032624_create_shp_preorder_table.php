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
        Schema::create('shp_preorder', function (Blueprint $table) {
            $table->id();
            $table->integer('shp_id')->nullable();
            $table->foreignId('product_id')->constrained('shp_products');
            $table->boolean('is_pre_order')->default(false);
            $table->integer('days_to_ship')->default(3);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('shp_products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shp_preorder');
    }
};
