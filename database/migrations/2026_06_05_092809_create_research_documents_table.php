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
        Schema::create('research_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('research_projects')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->longText('extracted_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_documents');
    }
};
