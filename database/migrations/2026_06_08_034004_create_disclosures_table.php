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
        Schema::create('disclosures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticker_id')->constrained('tickers')->cascadeOnDelete();
            $table->string('title');
            $table->string('category')->nullable();
            $table->date('date')->nullable();
            $table->string('source_url')->nullable();
            $table->longText('raw_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disclosures');
    }
};
