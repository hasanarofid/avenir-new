<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_views', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('research_meta_id');
            $table->timestampTz('viewed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('research_meta_id')->references('research_id')->on('research_metas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_views');
    }
};
