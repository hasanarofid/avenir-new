<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research', function (Blueprint $table) {
            $table->string('recommendation')->nullable();
            $table->string('target_price')->nullable();
            $table->string('upside')->nullable();
            $table->string('report_type')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->string('pdf_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('research', function (Blueprint $table) {
            $table->dropColumn([
                'recommendation',
                'target_price',
                'upside',
                'report_type',
                'is_premium',
                'pdf_path'
            ]);
        });
    }
};
