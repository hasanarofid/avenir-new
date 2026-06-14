<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_prices', function (Blueprint $table) {
            $table->id();
            $table->string('kode');
            $table->date('date');
            $table->decimal('open', 15, 2)->nullable();
            $table->decimal('high', 15, 2)->nullable();
            $table->decimal('low', 15, 2)->nullable();
            $table->decimal('close', 15, 2)->nullable();
            $table->decimal('last_price', 15, 2)->nullable();
            $table->bigInteger('volume')->nullable();
            $table->decimal('value', 20, 2)->nullable();
            $table->bigInteger('frequency')->nullable();
            $table->string('source')->nullable(); // vendor, manual
            $table->string('price_type')->default('close'); // close, delayed, realtime, manual
            $table->timestamps();
            
            $table->index(['kode', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_prices');
    }
};
