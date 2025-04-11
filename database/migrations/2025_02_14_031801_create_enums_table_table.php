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
    public function up()
    {
        // Create ENUMs
        DB::statement("CREATE TYPE shp_images_image_ratio_enum AS ENUM ('1:1', '3:4')");
        DB::statement("CREATE TYPE shp_logistics_shipping_fee_type_enum AS ENUM ('CUSTOM_PRICE', 'SIZE_SELECTION')");
        DB::statement("CREATE TYPE shp_products_product_status_enum AS ENUM ('NORMAL', 'UNLIST')");
        DB::statement("CREATE TYPE shp_products_condition_enum AS ENUM ('NEW', 'USED')");
        DB::statement("CREATE TYPE shp_products_description_type_enum AS ENUM ('normal', 'extended')");
        DB::statement("CREATE TYPE shp_products_warranty_time_enum AS ENUM ('ONE_YEAR', 'TWO_YEARS', 'OVER_TWO_YEARS')");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::statement("DROP TYPE IF EXISTS shp_images_image_ratio_enum");
        DB::statement("DROP TYPE IF EXISTS shp_logistics_shipping_fee_type_enum");
        DB::statement("DROP TYPE IF EXISTS shp_products_product_status_enum");
        DB::statement("DROP TYPE IF EXISTS shp_products_condition_enum");
        DB::statement("DROP TYPE IF EXISTS shp_products_description_type_enum");
        DB::statement("DROP TYPE IF EXISTS shp_products_warranty_time_enum");
    }
};
