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
        Schema::create('research_drafts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('research_projects')->cascadeOnDelete();
            $table->string('model_used', 50)->nullable();
            $table->json('structured_json')->nullable();
            $table->string('status', 20)->default('draft'); // draft, approved
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_drafts');
    }
};
