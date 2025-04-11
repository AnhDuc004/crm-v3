<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLeadIntegrationEmailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lead_integration_emails', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->text('subject', 16777215)->nullable();
			$table->text('body', 16777215)->nullable();
			$table->dateTime('dateadded');
			$table->integer('leadid');
			$table->integer('emailid');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('lead_integration_emails');
	}

}
