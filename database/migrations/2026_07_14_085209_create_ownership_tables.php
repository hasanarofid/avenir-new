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
        // Snapshot periods (to distinguish between Current and Previous data uploads)
        Schema::create('ownership_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('period_date');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        // Entities (Issuers, Investors)
        Schema::create('ownership_entities', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. E:BRPT or I:123
            $table->string('label');
            $table->string('ticker')->nullable();
            $table->string('kind')->nullable(); // issuer, investor, individual
            $table->string('norm')->nullable(); // normalized name
            $table->boolean('listed')->default(false);
            $table->boolean('also_investor')->default(false);
            $table->timestamps();
        });

        // Edges (Holdings)
        Schema::create('ownership_edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->nullable()->constrained('ownership_snapshots')->onDelete('cascade');
            $table->string('from_key'); // Investor
            $table->string('to_key');   // Issuer
            $table->string('ticker')->nullable();
            $table->string('issuer_name')->nullable();
            $table->string('investor_name')->nullable();
            $table->string('investor_raw')->nullable();
            $table->decimal('pct', 10, 4)->nullable(); // e.g., 5.1234
            $table->bigInteger('shares')->default(0);
            $table->string('classification')->nullable();
            $table->char('local_foreign', 1)->nullable(); // L or F
            $table->string('nationality')->nullable();
            $table->string('direction')->nullable(); // BUY, SELL, NEW, EXIT, UNCHANGED
            $table->bigInteger('delta_shares')->default(0);
            $table->decimal('delta_pct', 8, 4)->default(0);
            $table->timestamps();

            $table->index('from_key');
            $table->index('to_key');
            $table->index('snapshot_id');
        });

        // Changes (Diff between periods)
        Schema::create('ownership_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->nullable()->constrained('ownership_snapshots')->onDelete('cascade');
            $table->string('from_key');
            $table->string('to_key');
            $table->string('investor_name')->nullable();
            $table->string('ticker')->nullable();
            $table->string('direction')->nullable();
            $table->bigInteger('delta_shares')->default(0);
            $table->decimal('delta_pct', 8, 4)->default(0);
            $table->timestamps();

            $table->index('from_key');
            $table->index('to_key');
            $table->index('snapshot_id');
        });

        // Audits (Precomputed metrical data per issuer)
        Schema::create('ownership_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->nullable()->constrained('ownership_snapshots')->onDelete('cascade');
            $table->string('issuer_key');
            $table->integer('confidence')->nullable();
            $table->decimal('top1', 8, 4)->nullable();
            $table->string('control_label')->nullable();
            $table->decimal('hhi', 10, 4)->nullable();
            $table->integer('nakamoto50')->nullable();
            $table->decimal('residual', 8, 4)->nullable();
            $table->decimal('float_proxy', 8, 4)->nullable();
            $table->timestamps();

            $table->index('issuer_key');
            $table->index('snapshot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ownership_tables');
    }
};
