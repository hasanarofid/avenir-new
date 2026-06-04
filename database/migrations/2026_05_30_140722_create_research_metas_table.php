<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_metas', function (Blueprint $table) {
            $table->string('research_id')->primary(); // String ID from Supabase CSV
            $table->uuid('mitra_id')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('ticker')->nullable();
            $table->string('sector')->nullable();
            $table->string('type')->nullable();
            $table->date('published_at')->nullable();
            $table->string('author_type')->nullable();
            $table->uuid('author_id')->nullable();
            $table->string('author_display_name')->nullable();
            $table->timestamps();

            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_metas');
    }
};
