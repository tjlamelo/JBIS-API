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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agency_id')->nullable()->constrained('agencies')->nullOnDelete();

            $table->string('contact_reason', 32)->index();
            $table->string('full_name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();

            $table->dateTime('scheduled_at')->nullable()->index();
            $table->integer('duration_minutes')->default(30);

            $table->string('subject')->nullable();
            $table->text('message')->nullable();

            $table->foreignId('discovery_source_id')
                ->nullable()
                ->constrained('discovery_sources')
                ->nullOnDelete();
            $table->string('discovery_source_other', 255)->nullable();
            $table->string('utm_source', 128)->nullable();
            $table->string('utm_medium', 128)->nullable();
            $table->string('utm_campaign', 128)->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->enum('type', ['IN_PERSON', 'ONLINE', 'PHONE'])->nullable();
            $table->string('meeting_link')->nullable();

            $table->enum('status', ['PENDING', 'CONFIRMED', 'CANCELLED', 'COMPLETED', 'NOSHOW'])
                ->default('PENDING')
                ->index();

            $table->text('internal_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
