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
            $table->decimal('flow_momentum_v2_score', 8, 4)->nullable();
            $table->decimal('market_stress_composite', 8, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_stance_daily', function (Blueprint $table) {
            $table->dropColumn(['flow_momentum_v2_score', 'market_stress_composite']);
        });
    }
};
