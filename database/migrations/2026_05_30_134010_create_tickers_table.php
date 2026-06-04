<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickers', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique();
            $table->string('company_name');
            $table->string('sector')->nullable();
            $table->text('description')->nullable();
            $table->decimal('current_price', 15, 2)->nullable();
            $table->decimal('target_price', 15, 2)->nullable();
            $table->string('recommendation')->nullable(); // bullish, bearish, neutral
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickers');
    }
};
