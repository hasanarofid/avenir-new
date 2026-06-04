<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pool_config', function (Blueprint $table) {
            $table->integer('period_year');
            $table->integer('period_month');
            $table->decimal('pool_budget_idr', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestampTz('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();
            
            $table->primary(['period_year', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pool_config');
    }
};
