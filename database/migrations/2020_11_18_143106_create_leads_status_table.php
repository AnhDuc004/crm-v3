<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLeadsStatusTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('leads_status', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 50)->index('name');
			$table->integer('statusorder')->nullable();
			$table->string('color', 10)->nullable()->default('#28B8DA');
			$table->integer('isdefault')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('leads_status');
	}

}
