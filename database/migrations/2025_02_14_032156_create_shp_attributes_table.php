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
        Schema::create('shp_attributes', function (Blueprint $table) {
            $table->id();
            $table->integer('shp_id')->nullable();
            $table->foreignId('product_id')->constrained('shp_products');
            $table->json('attribute_value_list')->nullable();
            $table->string('original_value_name', 255)->nullable();
            $table->string('value_unit', 50)->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shp_attributes');
    }
};
