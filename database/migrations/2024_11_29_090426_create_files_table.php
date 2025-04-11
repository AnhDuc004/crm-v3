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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->integer('rel_id')->nullable(false);
            $table->string('rel_type')->nullable(false); 
            $table->string('file_name')->nullable(false); 
            $table->string('filetype')->nullable();
            $table->integer('visible_to_customer')->nullable(false);
            $table->string('attachment_key')->nullable();
            $table->string('external')->nullable();
            $table->string('external_link')->nullable();
            $table->string('thumbnail_link')->nullable();
            $table->integer('staff_id')->nullable(false);
            $table->integer('contact_id')->nullable();
            $table->integer('task_comment_id')->nullable(false);
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
        Schema::dropIfExists('files');
    }
};
