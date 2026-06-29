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
        Schema::create('economic_events', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('event');
            $table->string('impact')->default('Medium');
            $table->string('region')->nullable();
            $table->boolean('is_past')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('economic_events');
    }
};
