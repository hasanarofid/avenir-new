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
        Schema::dropIfExists('master_stocks');
        Schema::create('master_stocks', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name')->nullable();
            $table->string('sector')->nullable();
            $table->string('sub_industry')->nullable();
            $table->boolean('is_sharia')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_stocks');
    }
};
