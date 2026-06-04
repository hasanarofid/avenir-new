<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('title')->nullable();
            $table->string('url')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_new')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->integer('email_sent_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
