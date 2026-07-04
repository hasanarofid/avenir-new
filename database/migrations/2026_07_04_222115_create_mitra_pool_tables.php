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
        // 8.1 Database Table - partner_applications
        Schema::create('partner_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('domicile');
            $table->string('current_profession');
            $table->text('profile_url');
            $table->json('sector_specializations');
            $table->string('capital_market_experience');
            $table->boolean('has_research_experience');
            $table->text('research_sample_url');
            $table->text('research_sample_file')->nullable();
            $table->json('certifications')->nullable();
            $table->text('motivation');
            $table->text('stock_ownership_disclosure');
            $table->enum('status', ['Submitted', 'Under Review', 'Sample Requested', 'Interview / Discussion', 'Approved', 'Rejected', 'Suspended'])->default('Submitted');
            $table->uuid('reviewer_id')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // 8.2 Database Table - partner_profiles
        Schema::create('partner_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable(); // Assuming users table uses UUID or we just map logically
            $table->uuid('application_id')->nullable();
            $table->string('display_name');
            $table->text('bio')->nullable();
            $table->text('avatar_url')->nullable();
            $table->enum('analyst_level', ['Contributor', 'Verified Mitra Analyst', 'Avenir Select Analyst', 'Lead Sector Contributor'])->default('Contributor');
            $table->json('sector_focus')->nullable();
            $table->enum('contributor_status', ['Active', 'Inactive', 'Suspended'])->default('Active');
            $table->enum('compliance_status', ['Clear', 'Warning', 'Strike 1', 'Strike 2', 'Banned'])->default('Clear');
            $table->boolean('payout_enabled')->default(true);
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            
            // Note: Since users id is char(36) in db, leaving it as uuid.
            $table->foreign('application_id')->references('id')->on('partner_applications')->nullOnDelete();
        });

        // 8.3 Database Table - partner_content_metrics
        Schema::create('partner_content_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('partner_id');
            $table->uuid('content_id'); // Can be article or research ID
            $table->date('period_month');
            $table->integer('unique_paid_reads')->default(0);
            $table->decimal('read_completion_rate', 5, 2)->default(0);
            $table->integer('avg_read_time_seconds')->default(0);
            $table->integer('saves')->default(0);
            $table->integer('downloads')->default(0);
            $table->integer('watchlist_actions')->default(0);
            $table->integer('comments')->default(0);
            $table->integer('conversion_credit')->default(0);
            $table->decimal('retention_score', 8, 4)->default(0);
            $table->decimal('editorial_score', 8, 4)->default(0);
            $table->decimal('final_score', 8, 4)->default(0);
            $table->timestamps();

            $table->foreign('partner_id')->references('id')->on('partner_profiles')->cascadeOnDelete();
        });

        // 8.4 Database Table - partner_payout_periods
        Schema::create('partner_payout_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('period_month');
            $table->decimal('gross_subscription_revenue', 15, 2)->default(0);
            $table->decimal('net_subscription_revenue', 15, 2)->default(0);
            $table->decimal('pool_rate', 5, 4)->default(0.2000); // 20% default
            $table->decimal('mitra_pool_amount', 15, 2)->default(0);
            $table->decimal('total_partner_score', 15, 4)->default(0);
            $table->enum('status', ['Draft', 'Calculated', 'Approved', 'Paid'])->default('Draft');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // 8.5 Database Table - partner_payouts
        Schema::create('partner_payouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('payout_period_id');
            $table->uuid('partner_id');
            $table->decimal('partner_score', 15, 4)->default(0);
            $table->decimal('payout_amount', 15, 2)->default(0);
            $table->decimal('rollover_amount', 15, 2)->default(0);
            $table->enum('payout_status', ['Pending', 'Rollover', 'Processing', 'Paid', 'Failed'])->default('Pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->foreign('payout_period_id')->references('id')->on('partner_payout_periods')->cascadeOnDelete();
            $table->foreign('partner_id')->references('id')->on('partner_profiles')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_payouts');
        Schema::dropIfExists('partner_payout_periods');
        Schema::dropIfExists('partner_content_metrics');
        Schema::dropIfExists('partner_profiles');
        Schema::dropIfExists('partner_applications');
    }
};
