<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_depan')->nullable();
            $table->string('nama_belakang')->nullable();
            $table->string('profil_investor')->nullable();
            $table->timestamp('trial_started_at')->nullable();
            $table->timestamp('subscription_until')->nullable();
            $table->boolean('is_mitra')->default(false);
            $table->string('no_hp')->nullable();
            $table->string('sertifikasi')->nullable();
            $table->text('bio')->nullable();
            $table->jsonb('spesialisasi')->nullable();
            $table->string('link_portfolio')->nullable();
            $table->string('foto_url')->nullable();
            $table->boolean('is_user')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->string('bank_name')->nullable();
            $table->string('rekening_holder')->nullable();
            $table->string('rekening_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_profiles');
    }
};
