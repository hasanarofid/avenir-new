<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trial_email_history', function (Blueprint $table) {
            $table->string('email_lower')->primary();
            $table->timestampTz('first_trial_at')->nullable();
            $table->uuid('current_user_id')->nullable();
            $table->integer('registration_count')->default(0);
            $table->timestampTz('last_seen_at')->nullable();
            $table->timestampTz('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trial_email_history');
    }
};
