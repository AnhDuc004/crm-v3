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
        Schema::create('customfield', function (Blueprint $table) {
            $table->id();
            $table->string('field_to')->nullable(false);
            $table->string('name')->nullable(false);
            $table->string('slug')->nullable(false);
            $table->tinyInteger('required')->nullable(false);
            $table->string('type')->nullable(false);
            $table->longText('options')->nullable(); // Nếu có nhiều lựa chọn, có thể lưu dưới dạng JSON
            $table->tinyInteger('display_inline')->nullable(false);
            $table->integer('field_order')->nullable();
            $table->integer('active')->default(true);
            $table->integer('show_on_pdf')->nullable(false);
            $table->tinyInteger('show_on_ticket_form')->nullable(false);
            $table->tinyInteger('only_admin')->nullable(false);
            $table->tinyInteger('show_on_table')->nullable(false);
            $table->integer('show_on_client_portal')->nullable(false);
            $table->integer('disalow_client_to_edit')->nullable(false);
            $table->integer('bs_column')->nullable(false);
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
        Schema::dropIfExists('customfield');
    }
};
