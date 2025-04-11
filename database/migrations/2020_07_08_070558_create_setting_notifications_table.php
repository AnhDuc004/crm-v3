<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('time')->default(4)->comment('time start notification for task (hour)');
            $table->unsignedSmallInteger('number_notification')->default(4)->comment('number of notification for task');
            $table->unsignedSmallInteger('delay')->default(15)->comment('delay notifile (minute)');
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
        Schema::dropIfExists('setting_notifications');
    }
}
