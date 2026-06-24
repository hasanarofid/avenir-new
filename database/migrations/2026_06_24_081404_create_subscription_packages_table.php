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
        Schema::create('subscription_packages', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g. '1bulan'
            $table->string('name');
            $table->integer('price');
            $table->string('period_text'); // e.g. '/ bulan'
            $table->string('per_month_text')->nullable(); // e.g. '≈ Rp 133.000 / bulan'
            $table->string('save_text')->nullable(); // e.g. 'Hemat Rp 48.000 vs bulanan'
            $table->integer('duration_days');
            $table->string('badge')->nullable(); // e.g. 'Populer'
            $table->boolean('special_bg')->default(false);
            $table->string('image_path');
            $table->integer('discount_percent')->default(0);
            $table->timestamp('discount_end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_packages');
    }
};
