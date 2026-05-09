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
        Schema::create('user_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_key'); // hash fingerprint navigateur/appareil
            $table->string('device_name', 120)->nullable();
            $table->string('ip', 45);
            $table->string('last_ip', 45)->nullable();
            $table->string('user_agent');
            $table->boolean('is_trusted')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->unsignedInteger('login_count')->default(0);
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->json('risk_flags')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_alerted_at')->nullable();
            $table->string('last_token_name', 255)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'device_key']);
            $table->index(['user_id', 'last_seen_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
