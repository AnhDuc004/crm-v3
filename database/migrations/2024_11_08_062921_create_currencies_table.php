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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->nullable(false);
            $table->string('name')->nullable(false);
            $table->string('decimal_separator')->nullable();
            $table->string('thousand_separator')->nullable();
            $table->string('placement')->nullable();
            $table->tinyInteger('is_default')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
