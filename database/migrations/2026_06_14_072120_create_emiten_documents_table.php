<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emiten_documents', function (Blueprint $table) {
            $table->id();
            $table->string('kode');
            $table->string('document_type');
            $table->year('year');
            $table->string('quarter')->nullable();
            $table->string('period_label')->nullable();
            $table->boolean('is_audited')->default(false);
            $table->string('file_url');
            $table->string('file_name');
            $table->integer('file_size');
            $table->string('mime_type');
            $table->string('processing_status')->default('Uploaded');
            $table->string('extracted_text_path')->nullable();
            $table->foreignUuid('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('kode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emiten_documents');
    }
};
