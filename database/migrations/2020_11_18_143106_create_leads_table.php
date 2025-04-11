<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLeadsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('leads', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('hash', 65)->nullable();
			$table->string('name', 191)->index('name');
			$table->string('title', 100)->nullable();
			$table->string('company', 191)->nullable()->index('company');
			$table->text('description', 65535)->nullable();
			$table->integer('country')->default(0);
			$table->string('zip', 15)->nullable();
			$table->string('city', 100)->nullable();
			$table->string('state', 50)->nullable();
			$table->string('address', 100)->nullable();
			$table->integer('assigned')->default(0)->index('assigned');
			$table->dateTime('dateadded')->index('dateadded');
			$table->integer('from_form_id')->default(0)->index('from_form_id');
			$table->integer('status')->index('status');
			$table->integer('source')->index('source');
			$table->dateTime('lastcontact')->nullable()->index('lastcontact');
			$table->date('dateassigned')->nullable();
			$table->dateTime('last_status_change')->nullable();
			$table->integer('addedfrom');
			$table->string('email', 100)->nullable()->index('email');
			$table->string('website', 150)->nullable();
			$table->integer('leadorder')->nullable()->default(1)->index('leadorder');
			$table->string('phonenumber', 50)->nullable();
			$table->dateTime('date_converted')->nullable();
			$table->boolean('lost')->default(0);
			$table->integer('junk')->default(0);
			$table->integer('last_lead_status')->default(0);
			$table->boolean('is_imported_from_email_integration')->default(0);
			$table->string('email_integration_uid', 30)->nullable();
			$table->boolean('is_public')->default(0);
			$table->string('default_language', 40)->nullable();
			$table->integer('client_id')->default(0);
			
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('leads');
	}

}
