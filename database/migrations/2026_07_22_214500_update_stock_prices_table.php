<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_prices', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_prices', 'listed_shares')) {
                $table->bigInteger('listed_shares')->nullable()->after('frequency');
            }
            
            // Check if unique index exists to prevent duplicate key errors
            $indexExists = collect(DB::select("SHOW INDEXES FROM stock_prices"))
                ->pluck('Key_name')
                ->contains('stock_prices_kode_date_unique');
                
            if (!$indexExists) {
                $table->unique(['kode', 'date'], 'stock_prices_kode_date_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_prices', function (Blueprint $table) {
            if (Schema::hasColumn('stock_prices', 'listed_shares')) {
                $table->dropColumn('listed_shares');
            }
            
            $indexExists = collect(DB::select("SHOW INDEXES FROM stock_prices"))
                ->pluck('Key_name')
                ->contains('stock_prices_kode_date_unique');
                
            if ($indexExists) {
                $table->dropUnique('stock_prices_kode_date_unique');
            }
        });
    }
};
