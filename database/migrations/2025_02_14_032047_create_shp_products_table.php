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
        Schema::create('shp_products', function (Blueprint $table) {
            $table->id();
            $table->integer('shp_id')->nullable();
            $table->string('product_name', 255);
            $table->text('description');
            $table->float('weight');
            $table->string('product_status')->nullable();
            $table->json('price_info')->nullable();
            $table->float('current_price')->nullable();
            $table->float('original_price')->nullable();
            $table->string('condition')->nullable();
            $table->integer('category_id');
            $table->json('logistic_info')->nullable();
            $table->string('description_type')->nullable();
            $table->json('video_info')->nullable();
            $table->integer('product_dangerous')->default(0);
            $table->integer('brand_id')->nullable();
            $table->string('gtin_code', 14)->nullable();
            $table->json('extended_description')->nullable();
            $table->json('complaint_policy')->nullable();
            $table->string('warranty_time')->nullable();
            $table->boolean('exclude_entrepreneur_warranty')->nullable();
            $table->integer('complaint_address_id')->nullable();
            $table->text('additional_information')->nullable();
            $table->json('seller_stock')->nullable();
            $table->timestamp('scheduled_publish_time')->nullable();
            $table->integer('authorised_brand_id')->nullable();
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
        Schema::dropIfExists('shp_products');
    }
};
