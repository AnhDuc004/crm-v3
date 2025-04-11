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
        Schema::create('expenses', function (Blueprint $table) {
        $table->id();
        $table->integer('category')->nullable(false);
        $table->integer('currency')->nullable(false);
        $table->decimal('amount', 15, 2);
        $table->integer('tax', )->nullable();
        $table->integer('tax2', )->nullable();
        $table->string('reference_no', 255)->nullable();
        $table->text('note')->nullable();
        $table->string('expense_name', 255);
        $table->integer('customer_id')->nullable();
        $table->integer('project_id')->nullable();
        $table->integer('billable')->default(0);
        $table->integer('invoice_id')->nullable();
        $table->string('paymentmode', 255)->nullable();
        $table->date('date')->nullable();
        $table->string('recurring_type', 255)->nullable();
        $table->integer('repeat_every')->nullable();
        $table->integer('recurring')->default(0);
        $table->integer('cycles')->nullable();
        $table->integer('total_cycles')->nullable();
        $table->integer('custom_recurring')->default(0);
        $table->date('last_recurring_date')->nullable();
        $table->tinyInteger('create_invoice_billable')->default(0);
        $table->tinyInteger('send_invoice_to_customer')->default(0);
        $table->integer('recurring_from')->nullable();
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
        Schema::dropIfExists('expenses');
    }
};
