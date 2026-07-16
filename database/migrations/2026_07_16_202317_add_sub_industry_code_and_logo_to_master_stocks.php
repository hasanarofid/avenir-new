<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_stocks', function (Blueprint $table) {
            $table->string('sub_industry_code', 10)->nullable()->after('sub_industry');
            $table->string('logo_url')->nullable()->after('is_sharia');   // URL logo emiten
            $table->string('fiscal_year_end', 10)->nullable()->after('logo_url'); // e.g. "Dec", "Mar"
            $table->string('fs_date', 20)->nullable()->after('fiscal_year_end');  // e.g. "03/31/2026"
        });
    }

    public function down(): void
    {
        Schema::table('master_stocks', function (Blueprint $table) {
            $table->dropColumn(['sub_industry_code', 'logo_url', 'fiscal_year_end', 'fs_date']);
        });
    }
};
