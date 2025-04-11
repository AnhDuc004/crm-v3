<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description', 500)->nullable();
            $table->string('note', 500)->nullable();
            $table->unsignedBigInteger('project_id')->nullable()->comment('có thể thuộc dự án hoặc không');
            $table->unsignedBigInteger('task_type_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedTinyInteger('status')->default(1)->comment('1:new, 2:processing, 3:done, 4:fail, 5:cancel');
            $table->unsignedTinyInteger('priority')->default(1)->comment('1:normal, 2:high, 3:just now');
            $table->unsignedTinyInteger('potential')->default(2)->comment('1:low, 2:normal, 3:high, 4: very high');
            $table->dateTime('last_notification')->nullable()->comment('last time send notifiction');
            $table->unsignedSmallInteger('count_notification')->default(0)->comment('count notifiction');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->index('status');
            // $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('task_type_id')->references('id')->on('task_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
