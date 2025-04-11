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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->integer('isread')->nullable(false);
            $table->tinyInteger('is_read_inline')->nullable(false);;
            $table->text('description')->nullable(false);
            $table->integer('from_client_id')->nullable(false);
            $table->string('from_fullname')->nullable(false);
            $table->integer('to_user_id')->nullable(false);
            $table->integer('from_company')->nullable();
            $table->mediumText('link')->nullable();
            $table->text('additional_data')->nullable();
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
        Schema::dropIfExists('notifications');
    }
};
