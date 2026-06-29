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
        Schema::create('smart_money_flows', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('cumulative_net')->nullable();
            $table->string('cumulative_vs')->nullable();
            $table->string('cost_basis')->nullable();
            $table->string('price_vs_cost')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_money_flows');
    }
};
