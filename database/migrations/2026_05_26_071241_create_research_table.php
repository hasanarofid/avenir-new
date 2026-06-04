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
        Schema::create('research', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('ticker')->nullable();
            $table->string('sector')->nullable();
            $table->longText('subtitle')->nullable();
            $table->string('revenue')->nullable();
            $table->string('patmi')->nullable();
            $table->string('sales')->nullable();
            $table->string('tags')->nullable();
            $table->string('date')->nullable();
            $table->string('price')->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research');
    }
};
