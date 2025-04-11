<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignExeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_exe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('content_id');
            $table->unsignedBigInteger('image_id');
            $table->foreign('campaign_id')->references('id')->on('campaign')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('campaign_group')->onDelete('cascade');
            $table->foreign('content_id')->references('id')->on('campaign_content')->onDelete('cascade');
            $table->foreign('image_id')->references('id')->on('campaign_image')->onDelete('cascade');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign_exe');
    }
}
