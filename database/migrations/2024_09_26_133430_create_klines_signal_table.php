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
        Schema::create('klines_signals', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->integer('timeAt');
            $table->float('open')->nullable();
            $table->float('hight')->nullable();
            $table->float('low')->nullable();
            $table->float('close')->nullable();
            $table->float('loss')->nullable();
            $table->float('lossRate')->nullable();
            $table->integer('countdown')->nullable();
            $table->integer('buySignal')->nullable();
            $table->integer('timeBuy')->nullable();
            $table->float('priceBuy')->nullable();
            $table->float('priceGain')->nullable();
            $table->integer('timeSale')->nullable();
            $table->float('priceSale')->default(0);
            $table->integer('timelong')->nullable();
            $table->integer('saleStatus')->default(0);
            $table->integer('readStatus')->default(0);
            $table->float('amount')->nullable();
            $table->float('mgainer')->nullable();
            // $table->foreignId('user_id')->comment('user who created this data.');
            // $table->foreign('user_id')->references('id')->on('users');
            $table->integer('signal_id')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('klines_signals');
    }
};
