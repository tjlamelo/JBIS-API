<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('language', 5)->default('fr');
            $table->string('scope', 32)->default('both')->index();
            $table->string('status', 32)->default('subscribed')->index();
            $table->string('unsubscribe_token', 64)->unique();
            $table->string('source', 64)->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            $table->unique('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscriptions');
    }
};
