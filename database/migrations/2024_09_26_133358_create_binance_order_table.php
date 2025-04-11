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
        Schema::create('binance_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('user who created this data.');
            $table->integer('orderId')->default(0);
            $table->string('symbol', length: 20);
            $table->double('amount')->default(0);
            $table->double('price')->default(0);
            $table->string('side', length: 20);
            $table->float('quantity')->default(0);
            $table->boolean('acive');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('binance_order');
    }
};
