<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickers', function (Blueprint $table) {
            $table->string('sub_sektor')->nullable()->after('sector');
            $table->string('industri')->nullable()->after('sub_sektor');
            $table->string('papan_pencatatan')->nullable()->after('industri');
            $table->date('tanggal_listing')->nullable()->after('papan_pencatatan');
            $table->string('website')->nullable()->after('tanggal_listing');
            $table->string('logo_url')->nullable()->after('website');
            $table->text('business_summary')->nullable()->after('logo_url');
            $table->text('ticker_brief')->nullable()->after('business_summary');
            $table->text('risk_summary')->nullable()->after('ticker_brief');
            $table->text('investment_angle')->nullable()->after('risk_summary');
            $table->json('business_segments')->nullable()->after('investment_angle');
            $table->json('competitive_advantage')->nullable()->after('business_segments');
            $table->json('key_risks')->nullable()->after('competitive_advantage');
            $table->string('status')->default('Draft')->after('key_risks');
        });
    }

    public function down(): void
    {
        Schema::table('tickers', function (Blueprint $table) {
            $table->dropColumn([
                'sub_sektor', 'industri', 'papan_pencatatan', 'tanggal_listing',
                'website', 'logo_url', 'business_summary', 'ticker_brief',
                'risk_summary', 'investment_angle', 'business_segments',
                'competitive_advantage', 'key_risks', 'status'
            ]);
        });
    }
};
