<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emiten_ai_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('kode');
            $table->foreignId('document_id')->nullable()->constrained('emiten_documents')->onDelete('set null');
            $table->string('draft_type');
            $table->json('raw_json');
            $table->json('edited_json')->nullable();
            $table->integer('confidence_score')->nullable();
            $table->string('model_name');
            $table->string('prompt_version')->nullable();
            $table->string('status')->default('Generated');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index('kode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emiten_ai_drafts');
    }
};
