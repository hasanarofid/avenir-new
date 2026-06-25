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
        Schema::create('market_stance_daily', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->integer('score');
            $table->string('label');
            $table->integer('foreign_score')->nullable();
            $table->integer('breadth_score')->nullable();
            $table->integer('momentum_score')->nullable();
            $table->integer('rupiah_score')->nullable();
            $table->integer('yield_score')->nullable();
            $table->integer('sector_score')->nullable();
            $table->string('override_label')->nullable();
            $table->timestamps();
        });

        Schema::create('desk_briefs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('session_type')->default('after_market');
            $table->string('title')->nullable();
            $table->foreignId('market_stance_id')->nullable()->constrained('market_stance_daily')->nullOnDelete();
            $table->text('market_read')->nullable();
            $table->text('so_what')->nullable();
            $table->text('what_to_do')->nullable();
            $table->foreignUuid('analyst_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('desk_brief_drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brief_id')->constrained('desk_briefs')->cascadeOnDelete();
            $table->integer('rank')->default(1);
            $table->string('title');
            $table->string('category')->nullable();
            $table->string('source')->nullable();
            $table->string('impact_level')->nullable();
            $table->text('explanation')->nullable();
            $table->json('affected_sectors_json')->nullable();
            $table->json('affected_tickers_json')->nullable();
            $table->timestamps();
        });

        Schema::create('market_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('symbol_or_metric');
            $table->decimal('value', 15, 4)->nullable();
            $table->decimal('change_abs', 15, 4)->nullable();
            $table->decimal('change_pct', 8, 4)->nullable();
            $table->json('sparkline_json')->nullable();
            $table->string('source')->nullable();
            $table->timestamp('last_sync')->nullable();
            $table->timestamps();
            
            $table->unique(['date', 'symbol_or_metric']);
        });

        Schema::create('sector_bias_daily', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('sector');
            $table->string('bias');
            $table->text('reason')->nullable();
            $table->decimal('flow_value', 20, 2)->nullable();
            $table->decimal('return_1d', 8, 4)->nullable();
            $table->string('valuation_context')->nullable();
            $table->string('risk_context')->nullable();
            $table->timestamps();
            
            $table->unique(['date', 'sector']);
        });

        Schema::create('radar_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brief_id')->constrained('desk_briefs')->cascadeOnDelete();
            $table->string('ticker');
            $table->text('thesis')->nullable();
            $table->text('trigger')->nullable();
            $table->text('risk')->nullable();
            $table->string('view_label')->nullable();
            $table->string('action_label')->nullable();
            $table->decimal('target_price', 15, 2)->nullable();
            $table->integer('priority')->default(1);
            $table->timestamps();
        });

        Schema::create('risk_alerts', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('risk_type');
            $table->string('indicator')->nullable();
            $table->string('current_value')->nullable();
            $table->string('threshold')->nullable();
            $table->string('severity')->nullable();
            $table->text('impact')->nullable();
            $table->text('action_suggestion')->nullable();
            $table->timestamps();
        });

        Schema::create('api_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('endpoint');
            $table->string('status');
            $table->integer('credits_used')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_sync_logs');
        Schema::dropIfExists('risk_alerts');
        Schema::dropIfExists('radar_stocks');
        Schema::dropIfExists('sector_bias_daily');
        Schema::dropIfExists('market_snapshots');
        Schema::dropIfExists('desk_brief_drivers');
        Schema::dropIfExists('desk_briefs');
        Schema::dropIfExists('market_stance_daily');
    }
};
