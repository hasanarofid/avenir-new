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
        Schema::table('market_stance_daily', function (Blueprint $table) {
            $table->decimal('flow_exhaustion_score', 8, 4)->nullable();
            $table->decimal('reversal_probability', 8, 4)->nullable();
            $table->decimal('macro_stress', 8, 4)->nullable();
            $table->decimal('flow_internal_stress', 8, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_stance_daily', function (Blueprint $table) {
            $table->dropColumn([
                'flow_exhaustion_score',
                'reversal_probability',
                'macro_stress',
                'flow_internal_stress'
            ]);
        });
    }
};

