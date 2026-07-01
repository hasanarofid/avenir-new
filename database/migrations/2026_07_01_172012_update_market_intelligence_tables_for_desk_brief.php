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
        Schema::table('sector_bias_daily', function (Blueprint $table) {
            $table->integer('rotation_score')->default(0);
            $table->integer('smart_money_score')->default(0);
            $table->integer('valuation_score')->default(0);
            $table->integer('event_score')->default(0);
            $table->integer('confluence_score')->default(0);
            $table->string('confluence_label')->nullable();
        });

        Schema::create('smart_money_lens_daily', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('ticker');
            $table->string('type');
            $table->decimal('price_change_5d', 8, 4)->nullable();
            $table->decimal('flow_z_score', 8, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_money_lens_daily');

        Schema::table('sector_bias_daily', function (Blueprint $table) {
            $table->dropColumn([
                'rotation_score',
                'smart_money_score',
                'valuation_score',
                'event_score',
                'confluence_score',
                'confluence_label'
            ]);
        });
    }
};
