<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emiten_bank_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('kode');
            $table->string('period_type');
            $table->string('period');
            $table->year('year');
            $table->string('quarter')->nullable();
            
            $table->decimal('net_interest_income', 20, 2)->nullable();
            $table->decimal('operating_income', 20, 2)->nullable();
            $table->decimal('net_profit', 20, 2)->nullable();
            $table->decimal('loans', 20, 2)->nullable();
            $table->decimal('deposits', 20, 2)->nullable();
            $table->decimal('casa', 20, 2)->nullable();
            
            // Ratios
            $table->decimal('nim', 10, 4)->nullable();
            $table->decimal('npl_gross', 10, 4)->nullable();
            $table->decimal('npl_net', 10, 4)->nullable();
            $table->decimal('ldr', 10, 4)->nullable();
            $table->decimal('car', 10, 4)->nullable();
            $table->decimal('roa', 10, 4)->nullable();
            $table->decimal('roe', 10, 4)->nullable();
            $table->decimal('cir', 10, 4)->nullable();
            
            $table->foreignId('source_document_id')->nullable()->constrained('emiten_documents')->onDelete('set null');
            $table->string('validation_status')->default('Pending');
            $table->timestamps();
            
            $table->index(['kode', 'year', 'period_type', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emiten_bank_metrics');
    }
};
