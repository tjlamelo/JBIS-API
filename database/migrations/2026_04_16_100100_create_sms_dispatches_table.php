<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_dispatches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sms_campaign_id')->constrained('sms_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('phone_number', 30);
            $table->string('status', 20)->default('pending');
            $table->string('provider_message_id', 120)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['sms_campaign_id', 'phone_number']);
            $table->index(['sms_campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_dispatches');
    }
};
