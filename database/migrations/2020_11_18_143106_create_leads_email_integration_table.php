<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLeadsEmailIntegrationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('leads_email_integration', function(Blueprint $table)
		{
			$table->integer('id', true)->comment('the ID always must be 1');
			$table->integer('active');
			$table->string('email', 100);
			$table->string('imap_server', 100);
			$table->text('password', 16777215);
			$table->integer('check_every')->default(5);
			$table->integer('responsible');
			$table->integer('lead_source');
			$table->integer('lead_status');
			$table->string('encryption', 3)->nullable();
			$table->string('folder', 100);
			$table->string('last_run', 50)->nullable();
			$table->boolean('notify_lead_imported')->default(1);
			$table->boolean('notify_lead_contact_more_times')->default(1);
			$table->string('notify_type', 20)->nullable();
			$table->text('notify_ids', 16777215)->nullable();
			$table->integer('mark_public')->default(0);
			$table->boolean('only_loop_on_unseen_emails')->default(1);
			$table->integer('delete_after_import')->default(0);
			$table->integer('create_task_if_customer')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('leads_email_integration');
	}

}
