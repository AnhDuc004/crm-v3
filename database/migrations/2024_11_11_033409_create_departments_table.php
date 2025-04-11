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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->string('imap_username', 191)->nullable();
            $table->string('email', 100)->nullable();
            $table->tinyInteger('email_from_header')->default(0);
            $table->string('host', 150)->nullable();
            $table->mediumText('password')->nullable();
            $table->string('encryption', 3)->nullable();
            $table->integer('delete_after_import')->default(0);
            $table->mediumText('calendar_id')->nullable();
            $table->tinyInteger('hide_from_client')->default(0);
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
        Schema::dropIfExists('departments');
    }
};
