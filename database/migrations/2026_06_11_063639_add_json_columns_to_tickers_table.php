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
        Schema::table('tickers', function (Blueprint $table) {
            $table->json('company_profile')->nullable()->after('recommendation');
            $table->json('financial_highlights')->nullable()->after('company_profile');
            $table->json('financial_ratios')->nullable()->after('financial_highlights');
            $table->json('main_risks')->nullable()->after('financial_ratios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickers', function (Blueprint $table) {
            $table->dropColumn([
                'company_profile',
                'financial_highlights',
                'financial_ratios',
                'main_risks'
            ]);
        });
    }
};
