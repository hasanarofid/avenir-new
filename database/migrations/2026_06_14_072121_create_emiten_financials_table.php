<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emiten_financials', function (Blueprint $table) {
            $table->id();
            $table->string('kode');
            $table->string('period_type'); // Quarterly, Annual, TTM
            $table->string('period'); // Q1, Q2, Q3, Q4, FY
            $table->year('year');
            $table->string('quarter')->nullable();
            $table->string('currency')->default('IDR');
            $table->string('unit')->default('billion');
            
            // Income Statement
            $table->decimal('revenue', 20, 2)->nullable();
            $table->decimal('gross_profit', 20, 2)->nullable();
            $table->decimal('operating_profit', 20, 2)->nullable();
            $table->decimal('ebitda', 20, 2)->nullable();
            $table->decimal('profit_before_tax', 20, 2)->nullable();
            $table->decimal('net_profit', 20, 2)->nullable();
            $table->decimal('net_profit_parent', 20, 2)->nullable();
            $table->decimal('eps', 15, 2)->nullable();

            // Balance Sheet
            $table->decimal('total_assets', 20, 2)->nullable();
            $table->decimal('cash_and_equivalents', 20, 2)->nullable();
            $table->decimal('inventory', 20, 2)->nullable();
            $table->decimal('total_liabilities', 20, 2)->nullable();
            $table->decimal('interest_bearing_debt', 20, 2)->nullable();
            $table->decimal('total_equity', 20, 2)->nullable();
            $table->decimal('bvps', 15, 2)->nullable();

            // Cash Flow
            $table->decimal('operating_cash_flow', 20, 2)->nullable();
            $table->decimal('investing_cash_flow', 20, 2)->nullable();
            $table->decimal('financing_cash_flow', 20, 2)->nullable();
            $table->decimal('capex', 20, 2)->nullable();
            $table->decimal('free_cash_flow', 20, 2)->nullable();

            // Dividend & Market Data
            $table->decimal('dividend_per_share', 15, 2)->nullable();
            $table->decimal('payout_ratio', 10, 4)->nullable();
            $table->decimal('dividend_yield', 10, 4)->nullable();
            $table->decimal('market_cap', 20, 2)->nullable();
            $table->bigInteger('shares_outstanding')->nullable();
            $table->decimal('free_float', 10, 4)->nullable();
            
            $table->foreignId('source_document_id')->nullable()->constrained('emiten_documents')->onDelete('set null');
            $table->string('validation_status')->default('Pending');
            $table->timestamps();
            
            $table->index(['kode', 'year', 'period_type', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emiten_financials');
    }
};
