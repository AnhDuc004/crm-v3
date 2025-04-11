<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadActivityLogTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lead_activity_log', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('leadid');
			$table->text('description');
			$table->text('additional_data')->nullable();
			$table->dateTime('date');
			$table->integer('staffid');
			$table->string('full_name', 100)->nullable();
			$table->boolean('custom_activity')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('lead_activity_log');
	}
}
